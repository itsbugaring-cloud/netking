<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Services\MikroTikService;
use Illuminate\Console\Command;

class TestAllMikroTik extends Command
{
    protected $signature = 'mikrotik:test-all
                            {--area= : Test specific area by ID or name}
                            {--secrets : Also try to load PPPoE secrets count}
                            {--timeout=5 : Connection timeout in seconds}';

    protected $description = 'Test MikroTik API connection to ALL routers (or a specific one). Reports online/offline/timeout status.';

    public function handle(): int
    {
        $this->info('═══════════════════════════════════════════');
        $this->info('  MikroTik Multi-Router Connection Test');
        $this->info('═══════════════════════════════════════════');
        $this->newLine();

        $timeout = (int) $this->option('timeout');
        $checkSecrets = $this->option('secrets');

        // Get areas to test
        $query = Area::whereNotNull('router_ip')->where('router_ip', '!=', '');

        if ($this->option('area')) {
            $filter = $this->option('area');
            $query->where(function ($q) use ($filter) {
                $q->where('id', $filter)->orWhere('name', 'LIKE', "%{$filter}%");
            });
        }

        $areas = $query->orderBy('name')->get();

        if ($areas->isEmpty()) {
            $this->warn('No areas with router_ip found.');
            return 1;
        }

        $this->info("Testing {$areas->count()} router(s) with {$timeout}s timeout...");
        $this->newLine();

        $results = [];
        $online = 0;
        $offline = 0;

        foreach ($areas as $area) {
            $this->output->write("  [{$area->id}] {$area->name} ({$area->router_ip}) ... ");

            $startTime = microtime(true);
            $mikrotik = new MikroTikService(
                $area->router_ip,
                $area->router_user,
                $area->router_pass,
                8728
            );

            $testResult = $mikrotik->testConnection();
            $elapsed = round((microtime(true) - $startTime) * 1000);

            $row = [
                'area_id' => $area->id,
                'name' => $area->name,
                'ip' => $area->router_ip,
                'elapsed_ms' => $elapsed,
                'identity' => null,
                'secrets' => null,
                'status' => 'OFFLINE',
                'error' => null,
            ];

            if ($testResult['success']) {
                $row['status'] = 'ONLINE';
                $row['identity'] = $testResult['identity'] ?? 'Unknown';
                $online++;

                $this->info("✓ ONLINE ({$elapsed}ms) — Identity: {$row['identity']}");

                // Optionally load secrets count
                if ($checkSecrets) {
                    $this->output->write("       Fetching secrets... ");
                    $secretsStart = microtime(true);
                    $secretsResult = $mikrotik->getAllSecrets();
                    $secretsElapsed = round((microtime(true) - $secretsStart) * 1000);

                    if ($secretsResult['success']) {
                        $count = count($secretsResult['data']);
                        $row['secrets'] = $count;
                        $this->info("✓ {$count} secrets ({$secretsElapsed}ms)");
                    } else {
                        $row['secrets'] = -1;
                        $this->error("✗ Failed ({$secretsElapsed}ms): " . ($secretsResult['error'] ?? 'Unknown'));
                    }
                }
            } else {
                $row['error'] = $testResult['error'] ?? 'Connection failed';
                $offline++;

                // Categorize the error
                $errorMsg = $row['error'];
                if (str_contains($errorMsg, 'timed out') || str_contains($errorMsg, 'timeout')) {
                    $row['status'] = 'TIMEOUT';
                    $this->error("✗ TIMEOUT ({$elapsed}ms) — Router unreachable");
                } elseif (str_contains($errorMsg, 'refused')) {
                    $row['status'] = 'REFUSED';
                    $this->error("✗ REFUSED ({$elapsed}ms) — Port 8728 closed/blocked");
                } elseif (str_contains($errorMsg, 'credentials') || str_contains($errorMsg, 'login') || str_contains($errorMsg, 'invalid user')) {
                    $row['status'] = 'AUTH_FAIL';
                    $this->error("✗ AUTH FAILED ({$elapsed}ms) — Wrong username/password");
                } else {
                    $this->error("✗ OFFLINE ({$elapsed}ms) — {$errorMsg}");
                }
            }

            $results[] = $row;
        }

        // Summary
        $this->newLine();
        $this->info('═══════════════════════════════════════════');
        $this->info('  SUMMARY');
        $this->info('═══════════════════════════════════════════');
        $this->info("  Total routers: {$areas->count()}");
        $this->info("  Online:        {$online}");

        if ($offline > 0) {
            $this->error("  Offline:       {$offline}");
        } else {
            $this->info("  Offline:       0");
        }

        // Show table for offline routers
        $offlineResults = array_filter($results, fn($r) => $r['status'] !== 'ONLINE');
        if (!empty($offlineResults)) {
            $this->newLine();
            $this->warn('  Problematic Routers:');
            $this->table(
                ['ID', 'Area', 'IP', 'Status', 'Time', 'Error'],
                array_map(fn($r) => [
                    $r['area_id'],
                    $r['name'],
                    $r['ip'],
                    $r['status'],
                    $r['elapsed_ms'] . 'ms',
                    \Illuminate\Support\Str::limit($r['error'] ?? '', 50),
                ], $offlineResults)
            );

            // Diagnosis hints
            $this->newLine();
            $this->info('  Diagnosis Hints:');
            foreach ($offlineResults as $r) {
                $this->line("  [{$r['name']}]");
                match ($r['status']) {
                    'TIMEOUT' => $this->line("    → Router unreachable. Check: VPN/WireGuard up? Firewall allows port 8728? IP correct?"),
                    'REFUSED' => $this->line("    → Port 8728 is closed. Enable API service: /ip service enable api"),
                    'AUTH_FAIL' => $this->line("    → Wrong credentials. Verify router_user/router_pass in Areas admin panel."),
                    default => $this->line("    → Check network connectivity to {$r['ip']}"),
                };
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════');

        return $offline > 0 ? 1 : 0;
    }
}
