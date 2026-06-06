<?php

namespace App\Console\Commands;

use App\Models\Ipam\IpamOlt;
use App\Models\Ipam\IpamRouter;
use App\Models\Ipam\IpamSubnet;
use App\Services\Ipam\MikroTikScannerService;
use Illuminate\Console\Command;

class IpamVerify extends Command
{
    protected $signature = 'ipam:verify';

    protected $description = 'Verify IPAM migration completeness and router connectivity from VM 103';

    public function handle(MikroTikScannerService $scanner): int
    {
        $this->info('═══ IPAM Migration Verification ═══');
        $this->newLine();

        $checks = [];

        // 1. Check routers
        $routerCount = IpamRouter::count();
        $checks[] = [
            'Check' => 'Routers in database',
            'Result' => $routerCount > 0 ? "✓ PASS ({$routerCount} records)" : '✗ FAIL (empty)',
        ];

        // 2. Check OLTs
        $oltCount = IpamOlt::count();
        $checks[] = [
            'Check' => 'OLTs in database',
            'Result' => $oltCount > 0 ? "✓ PASS ({$oltCount} records)" : '✗ FAIL (empty)',
        ];

        // 3. Check subnets
        $subnetCount = IpamSubnet::count();
        $checks[] = [
            'Check' => 'Subnets in database',
            'Result' => $subnetCount > 0 ? "✓ PASS ({$subnetCount} records)" : '⚠ WARN (empty, optional)',
        ];

        $this->table(['Check', 'Result'], $checks);
        $this->newLine();

        // 4. Router connectivity
        if ($routerCount === 0) {
            $this->warn('No routers to test connectivity.');
            return self::SUCCESS;
        }

        $this->info('Testing router connectivity...');
        $routers = IpamRouter::all();
        $bar = $this->output->createProgressBar($routers->count());

        $reachable = 0;
        $unreachable = 0;
        $connectivityResults = [];

        foreach ($routers as $router) {
            $online = $scanner->healthCheck($router);

            if ($online) {
                $reachable++;
                $connectivityResults[] = [
                    'Router' => $router->device_name,
                    'IP' => $router->wireguard_ip,
                    'Status' => '✓ Connected',
                ];
            } else {
                $unreachable++;
                $connectivityResults[] = [
                    'Router' => $router->device_name,
                    'IP' => $router->wireguard_ip,
                    'Status' => '✗ Unreachable',
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Router', 'IP', 'Status'], $connectivityResults);
        $this->newLine();

        // Summary
        $this->info("Summary: {$reachable} reachable, {$unreachable} unreachable out of {$routers->count()} routers");
        $this->newLine();

        if ($unreachable === 0) {
            $this->info('═══════════════════════════════════════════════════');
            $this->info('✓ Ready to decommission CT 100');
            $this->info('═══════════════════════════════════════════════════');
            return self::SUCCESS;
        }

        $this->warn('═══════════════════════════════════════════════════');
        $this->warn('✗ Some routers are unreachable — verify WireGuard');
        $this->warn('  connectivity on VM 103 before decommissioning.');
        $this->warn('═══════════════════════════════════════════════════');

        return self::FAILURE;
    }
}
