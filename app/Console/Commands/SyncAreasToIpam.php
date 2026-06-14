<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Ipam\IpamRouter;
use App\Services\Ipam\IpamAuditService;
use Illuminate\Console\Command;

class SyncAreasToIpam extends Command
{
    protected $signature   = 'ipam:sync-areas';
    protected $description = 'Sync all existing Area MikroTik routers into IPAM Routers table';

    public function handle(): int
    {
        $areas   = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->get();
        $created = 0;
        $updated = 0;
        $failed  = 0;

        $this->info("Found {$areas->count()} areas with router_ip. Syncing...");

        foreach ($areas as $area) {
            try {
                $router = IpamRouter::firstOrNew(['wireguard_ip' => $area->router_ip]);
                $isNew  = !$router->exists;

                $router->device_name   = $area->router_identity ?: $area->name;
                $router->auth_username = $area->router_user;
                if (!empty($area->router_pass)) {
                    $router->auth_password = $area->router_pass;
                }
                $router->save();

                $action = $isNew ? 'import' : 'update';
                IpamAuditService::log(
                    $action, 'router', $router->id,
                    ($isNew ? 'Batch-synced from Area: ' : 'Batch-updated from Area: ')
                    . "{$router->device_name} ({$area->router_ip})"
                );

                if ($isNew) {
                    $created++;
                    $this->line("  <fg=green>✓ Created:</> {$router->device_name} ({$area->router_ip})");
                } else {
                    $updated++;
                    $this->line("  <fg=blue>~ Updated:</> {$router->device_name} ({$area->router_ip})");
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->line("  <fg=red>✗ Failed:</> {$area->name} ({$area->router_ip}) — {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Done! Created: {$created}, Updated: {$updated}, Failed: {$failed}");

        return self::SUCCESS;
    }
}
