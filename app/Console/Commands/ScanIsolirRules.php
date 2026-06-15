<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Services\MikroTikService;
use Illuminate\Console\Command;
use Throwable;

class ScanIsolirRules extends Command
{
    protected $signature = 'mikrotik:scan-isolir';

    protected $description = 'Scan seluruh MikroTik (Area) untuk mengecek ketersediaan Firewall Rule Isolir';

    public function handle(): int
    {
        $this->info("Memulai proses scanning Firewall Isolir di seluruh MikroTik...\n");

        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->get();

        if ($areas->isEmpty()) {
            $this->error('Tidak ada area dengan konfigurasi IP Router yang valid.');
            return self::FAILURE;
        }

        foreach ($areas as $area) {
            $this->line("<fg=cyan>========================================</>");
            $this->line("<fg=cyan>Area    : {$area->name}</>");
            $this->line("<fg=cyan>IP      : {$area->router_ip}</>");
            $this->line("<fg=cyan>========================================</>\n");

            try {
                $mikrotik = MikroTikService::forArea($area);
                $client = $mikrotik->getClient();

                // 1. Scan Filter Rules
                $filterRules = $client->query(
                    (new \RouterOS\Query('/ip/firewall/filter/print'))
                )->read();

                $filterIsolir = collect($filterRules)->filter(function ($rule) {
                    $src = $rule['src-address-list'] ?? '';
                    $comment = strtolower($rule['comment'] ?? '');
                    return str_contains(strtolower($src), 'isolir') || str_contains($comment, 'isolir');
                });

                // 2. Scan NAT Rules
                $natRules = $client->query(
                    (new \RouterOS\Query('/ip/firewall/nat/print'))
                )->read();

                $natIsolir = collect($natRules)->filter(function ($rule) {
                    $src = $rule['src-address-list'] ?? '';
                    $comment = strtolower($rule['comment'] ?? '');
                    return str_contains(strtolower($src), 'isolir') || str_contains($comment, 'isolir');
                });

                // 3. Scan Mangle Rules
                $mangleRules = $client->query(
                    (new \RouterOS\Query('/ip/firewall/mangle/print'))
                )->read();

                $mangleIsolir = collect($mangleRules)->filter(function ($rule) {
                    $src = $rule['src-address-list'] ?? '';
                    $comment = strtolower($rule['comment'] ?? '');
                    return str_contains(strtolower($src), 'isolir') || str_contains($comment, 'isolir');
                });

                // Tampilkan Hasil
                $this->info("🔥 HASIL SCAN:");
                
                if ($filterIsolir->isEmpty() && $natIsolir->isEmpty() && $mangleIsolir->isEmpty()) {
                    $this->warn("  [KOSONG] Tidak ada satupun rule Isolir ditemukan di router ini.");
                } else {
                    if ($filterIsolir->isNotEmpty()) {
                        $this->line("  [FILTER] Ditemukan {$filterIsolir->count()} rule:");
                        foreach ($filterIsolir as $rule) {
                            $action = $rule['action'] ?? 'unknown';
                            $chain = $rule['chain'] ?? 'unknown';
                            $comment = $rule['comment'] ?? '-';
                            $status = isset($rule['disabled']) && $rule['disabled'] === 'true' ? '<fg=red>(Disabled)</>' : '<fg=green>(Active)</>';
                            $this->line("    - Action: {$action}, Chain: {$chain}, Comment: {$comment} {$status}");
                        }
                    }

                    if ($natIsolir->isNotEmpty()) {
                        $this->line("  [NAT] Ditemukan {$natIsolir->count()} rule:");
                        foreach ($natIsolir as $rule) {
                            $action = $rule['action'] ?? 'unknown';
                            $chain = $rule['chain'] ?? 'unknown';
                            $comment = $rule['comment'] ?? '-';
                            $status = isset($rule['disabled']) && $rule['disabled'] === 'true' ? '<fg=red>(Disabled)</>' : '<fg=green>(Active)</>';
                            $this->line("    - Action: {$action}, Chain: {$chain}, Comment: {$comment} {$status}");
                        }
                    }

                    if ($mangleIsolir->isNotEmpty()) {
                        $this->line("  [MANGLE] Ditemukan {$mangleIsolir->count()} rule:");
                        foreach ($mangleIsolir as $rule) {
                            $action = $rule['action'] ?? 'unknown';
                            $chain = $rule['chain'] ?? 'unknown';
                            $comment = $rule['comment'] ?? '-';
                            $status = isset($rule['disabled']) && $rule['disabled'] === 'true' ? '<fg=red>(Disabled)</>' : '<fg=green>(Active)</>';
                            $this->line("    - Action: {$action}, Chain: {$chain}, Comment: {$comment} {$status}");
                        }
                    }
                }

                $this->newLine();

            } catch (Throwable $e) {
                $this->error("❌ GAGAL KONEK: " . $e->getMessage() . "\n");
            }
        }

        $this->info("✨ Scanning Selesai!");
        return self::SUCCESS;
    }
}
