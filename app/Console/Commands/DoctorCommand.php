<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Area;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DoctorCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:doctor 
                            {--fix : Automatically fix issues where possible}
                            {--verbose : Show detailed information}';

    /**
     * The console command description.
     */
    protected $description = 'Run deep diagnostic checks on database integrity and find conflicts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('===========================================');
        $this->info('   NETKING ISP - System Health Check');
        $this->info('===========================================');
        $this->newLine();

        $fix = $this->option('fix');
        $verbose = $this->option('verbose');

        $issuesFound = 0;

        // Check 1: Duplicate IPs
        $issuesFound += $this->checkDuplicateIPs($fix, $verbose);

        // Check 2: Duplicate PPPoE Usernames
        $issuesFound += $this->checkDuplicatePPPoE($fix, $verbose);

        // Check 3: IPs outside Area Pool
        $issuesFound += $this->checkIPsOutsidePool($fix, $verbose);

        // Check 4: Orphaned Customers
        $issuesFound += $this->checkOrphanedCustomers($fix, $verbose);

        // Check 5: Missing Package References
        $issuesFound += $this->checkMissingPackages($fix, $verbose);

        // Summary
        $this->newLine();
        $this->info('===========================================');
        if ($issuesFound === 0) {
            $this->info('✅ All checks passed! System is healthy.');
        } else {
            $this->warn("⚠️  Found {$issuesFound} issue(s).");
            if (!$fix) {
                $this->info('Run with --fix to automatically resolve issues.');
            }
        }
        $this->info('===========================================');

        return 0;
    }

    /**
     * Check 1: Find customers with duplicate Remote IPs
     */
    protected function checkDuplicateIPs($fix, $verbose)
    {
        $this->info('[1] Checking for Duplicate Remote IPs...');

        $duplicates = Customer::select('remote_ip', DB::raw('COUNT(*) as count'))
            ->whereNotNull('remote_ip')
            ->groupBy('remote_ip')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->line('   ✅ No duplicate IPs found.');
            return 0;
        }

        $this->warn("   ⚠️  Found {$duplicates->count()} duplicate IP(s):");

        foreach ($duplicates as $dup) {
            $customers = Customer::where('remote_ip', $dup->remote_ip)->get();
            $this->line("   IP: {$dup->remote_ip} -> Used by {$dup->count} customers");

            if ($verbose) {
                foreach ($customers as $customer) {
                    $this->line("      - ID {$customer->id}: {$customer->name} ({$customer->pppoe_user})");
                }
            }

            if ($fix) {
                // Keep the first customer, re-allocate IPs for others
                $first = true;
                foreach ($customers as $customer) {
                    if ($first) {
                        $first = false;
                        continue;
                    }

                    $area = Area::find($customer->area_id);
                    if ($area) {
                        $newIp = $this->getNextAvailableIp($area);
                        $customer->update(['remote_ip' => $newIp]);
                        $this->line("      ✓ Re-allocated IP {$newIp} to customer ID {$customer->id}");
                    }
                }
            }
        }

        return $duplicates->count();
    }

    /**
     * Check 2: Find customers with duplicate PPPoE usernames
     */
    protected function checkDuplicatePPPoE($fix, $verbose)
    {
        $this->info('[2] Checking for Duplicate PPPoE Usernames...');

        $duplicates = Customer::select('pppoe_user', DB::raw('COUNT(*) as count'))
            ->groupBy('pppoe_user')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->line('   ✅ No duplicate PPPoE usernames found.');
            return 0;
        }

        $this->warn("   ⚠️  Found {$duplicates->count()} duplicate username(s):");

        foreach ($duplicates as $dup) {
            $customers = Customer::where('pppoe_user', $dup->pppoe_user)->get();
            $this->line("   Username: {$dup->pppoe_user} -> Used by {$dup->count} customers");

            if ($verbose) {
                foreach ($customers as $customer) {
                    $this->line("      - ID {$customer->id}: {$customer->name}");
                }
            }

            if ($fix) {
                $this->warn("   ⚠️  Cannot auto-fix duplicate usernames. Manual intervention required.");
                $this->line("      Please manually update duplicate usernames for:");
                foreach ($customers as $idx => $customer) {
                    if ($idx > 0) { // Skip first one
                        $this->line("      - Customer ID {$customer->id}: {$customer->name}");
                    }
                }
            }
        }

        return $duplicates->count();
    }

    /**
     * Check 3: Find IPs outside allowed Area Pool range
     */
    protected function checkIPsOutsidePool($fix, $verbose)
    {
        $this->info('[3] Checking for IPs Outside Area Pool...');

        $issues = 0;
        $areas = Area::all();

        foreach ($areas as $area) {
            if (!$area->ip_pool_start || !$area->ip_pool_end) {
                continue; // Skip areas without IP pool
            }

            $customers = Customer::where('area_id', $area->id)
                ->whereNotNull('remote_ip')
                ->get();

            $startLong = ip2long($area->ip_pool_start);
            $endLong = ip2long($area->ip_pool_end);

            foreach ($customers as $customer) {
                $ipLong = ip2long($customer->remote_ip);
                if ($ipLong < $startLong || $ipLong > $endLong) {
                    $issues++;
                    $this->warn("   ⚠️ Customer ID {$customer->id} ({$customer->name}): IP {$customer->remote_ip} is outside area pool {$area->ip_pool_start} - {$area->ip_pool_end}");

                    if ($fix) {
                        $newIp = $this->getNextAvailableIp($area);
                        $customer->update(['remote_ip' => $newIp]);
                        $this->line("      ✓ Fixed: Re-allocated IP {$newIp}");
                    }
                }
            }
        }

        if ($issues === 0) {
            $this->line('   ✅ All IPs are within their respective area pools.');
        }

        return $issues;
    }

    /**
     * Check 4: Find orphaned customers (linked to deleted partners)
     */
    protected function checkOrphanedCustomers($fix, $verbose)
    {
        $this->info('[4] Checking for Orphaned Customers...');

        $orphaned = Customer::whereNotNull('partner_id')
            ->whereNotIn('partner_id', User::where('role', 'partner')->pluck('id'))
            ->get();

        if ($orphaned->isEmpty()) {
            $this->line('   ✅ No orphaned customers found.');
            return 0;
        }

        $this->warn("   ⚠️  Found {$orphaned->count()} orphaned customer(s):");

        foreach ($orphaned as $customer) {
            $this->line("   Customer ID {$customer->id}: {$customer->name} -> Partner ID {$customer->partner_id} (deleted)");

            if ($fix) {
                $customer->update(['partner_id' => null]);
                $this->line("      ✓ Fixed: Set partner_id to NULL");
            }
        }

        return $orphaned->count();
    }

    /**
     * Check 5: Find customers with missing package references
     */
    protected function checkMissingPackages($fix, $verbose)
    {
        $this->info('[5] Checking for Missing Package References...');

        // Assuming packages table exists
        if (!Schema::hasTable('packages')) {
            $this->line('   ⚠️  Packages table not found. Skipping check.');
            return 0;
        }

        $invalid = Customer::whereNotNull('package_id')
            ->whereNotIn('package_id', DB::table('packages')->pluck('id'))
            ->get();

        if ($invalid->isEmpty()) {
            $this->line('   ✅ All customers have valid package references.');
            return 0;
        }

        $this->warn("   ⚠️  Found {$invalid->count()} customer(s) with invalid package:");

        foreach ($invalid as $customer) {
            $this->line("   Customer ID {$customer->id}: {$customer->name} -> Package ID {$customer->package_id} (not found)");

            if ($fix) {
                $this->warn("      ⚠️  Cannot auto-fix. Please assign a valid package manually.");
            }
        }

        return $invalid->count();
    }

    /**
     * Helper: Get next available IP from area pool
     */
    protected function getNextAvailableIp(Area $area)
    {
        $startIp = $area->ip_pool_start;
        $endIp = $area->ip_pool_end;

        $startLong = ip2long($startIp);
        $endLong = ip2long($endIp);

        if ($startLong === false || $endLong === false) {
            throw new \Exception("Invalid IP pool range for area {$area->name}");
        }

        $assignedIps = Customer::where('area_id', $area->id)
            ->whereNotNull('remote_ip')
            ->pluck('remote_ip')
            ->toArray();

        $usedIpsLong = array_map('ip2long', $assignedIps);

        for ($ipLong = $startLong; $ipLong <= $endLong; $ipLong++) {
            if (!in_array($ipLong, $usedIpsLong)) {
                return long2ip($ipLong);
            }
        }

        throw new \Exception("No available IPs in area {$area->name}");
    }
}
