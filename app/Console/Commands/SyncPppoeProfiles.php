<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Customer;
use App\Models\Package;
use App\Services\MikroTikService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncPppoeProfiles extends Command
{
    protected $signature = 'pppoe:sync-profiles
        {--area_id= : Sync hanya untuk 1 area ID}
        {--apply : Terapkan perubahan (default: dry-run)}
        {--reconcile : Setelah sync, assign package_id ke customer yang belum punya}
        {--skip-default : Skip profile "default" dari sync}';

    protected $description = 'Sync PPPoE profiles dari semua router MikroTik ke packages table, lalu reconcile customer tanpa package_id';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $reconcile = (bool) $this->option('reconcile');
        $skipDefault = (bool) $this->option('skip-default');
        $areaId = $this->option('area_id');

        $areasQuery = Area::query()
            ->whereNotNull('router_ip')
            ->where('router_ip', '!=', '');

        if ($areaId !== null && $areaId !== '') {
            $areasQuery->where('id', (int) $areaId);
        }

        $areas = $areasQuery->orderBy('name')->get();

        if ($areas->isEmpty()) {
            $this->warn('Tidak ada area dengan router_ip yang valid.');
            return self::SUCCESS;
        }

        $this->info('==============================================');
        $this->info('  PPPoE Profile Sync: Router → Packages');
        $this->info('==============================================');
        $this->line('Mode: ' . ($apply ? 'APPLY' : 'DRY-RUN'));
        $this->line('Reconcile customer: ' . ($reconcile ? 'YA' : 'TIDAK'));
        $this->line('Skip default profile: ' . ($skipDefault ? 'YA' : 'TIDAK'));
        $this->newLine();

        $totalCreated = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        $totalReconciled = 0;
        $failedAreas = 0;

        foreach ($areas as $area) {
            $this->line("━━━ Area #{$area->id} — {$area->name} ({$area->router_ip})");

            $mikrotik = MikroTikService::forArea($area);
            $test = $mikrotik->testConnection();

            if (!($test['success'] ?? false)) {
                $failedAreas++;
                $this->error("  ✗ Gagal konek: " . ($test['error'] ?? 'Unknown'));
                $this->newLine();
                continue;
            }

            // 1. Get PPPoE profiles from router
            $profilesResult = $mikrotik->getPppoeProfiles();
            if (!($profilesResult['success'] ?? false)) {
                $failedAreas++;
                $this->error("  ✗ Gagal ambil profiles: " . ($profilesResult['error'] ?? 'Unknown'));
                $this->newLine();
                continue;
            }

            $profiles = collect($profilesResult['data'] ?? []);
            $this->line("  Profiles dari router: {$profiles->count()}");

            $areaCreated = 0;
            $areaUpdated = 0;
            $areaSkipped = 0;

            foreach ($profiles as $profile) {
                $profileName = trim($profile['name'] ?? '');
                if ($profileName === '') {
                    continue;
                }

                // Skip default/encryption profiles
                if ($skipDefault && in_array(mb_strtolower($profileName), ['default', 'default-encryption'])) {
                    $areaSkipped++;
                    continue;
                }

                // Parse rate-limit: format "rx/tx" e.g. "10M/10M" or "10000000/5000000"
                $rateLimit = $profile['rate-limit'] ?? '';
                [$speedDown, $speedUp] = $this->parseRateLimit($rateLimit);

                // Check existing package by mikrotik_profile + area_id
                $existing = Package::where('area_id', $area->id)
                    ->whereRaw('LOWER(TRIM(mikrotik_profile)) = ?', [mb_strtolower($profileName)])
                    ->first();

                if ($existing) {
                    // Update speed if changed and we have valid speed data
                    $changes = [];
                    if ($speedDown > 0 && (int) $existing->speed_down !== $speedDown) {
                        $changes['speed_down'] = $speedDown;
                    }
                    if ($speedUp > 0 && (int) $existing->speed_up !== $speedUp) {
                        $changes['speed_up'] = $speedUp;
                    }

                    if (!empty($changes)) {
                        if ($apply) {
                            $existing->update($changes);
                        }
                        $areaUpdated++;
                        $this->line("  ↻ Updated: {$profileName} → " . implode(', ', array_map(fn($k, $v) => "{$k}={$v}", array_keys($changes), $changes)));
                    } else {
                        $areaSkipped++;
                    }
                } else {
                    // Create new package
                    $code = $this->generateCode($profileName, $area);

                    if ($apply) {
                        Package::create([
                            'name' => $profileName,
                            'code' => $code,
                            'speed_down' => $speedDown ?: 0,
                            'speed_up' => $speedUp ?: 0,
                            'price' => 0, // harus diisi manual nanti
                            'type' => 'residential',
                            'mikrotik_profile' => $profileName,
                            'is_active' => true,
                            'area_id' => $area->id,
                        ]);
                    }
                    $areaCreated++;
                    $this->line("  + New: {$profileName} (speed: {$speedDown}M/{$speedUp}M, code: {$code})");
                }
            }

            $totalCreated += $areaCreated;
            $totalUpdated += $areaUpdated;
            $totalSkipped += $areaSkipped;

            $this->line("  → Created: {$areaCreated} | Updated: {$areaUpdated} | Skipped: {$areaSkipped}");

            // 2. Reconcile customers without package_id
            if ($reconcile) {
                $reconCount = $this->reconcileArea($area, $apply);
                $totalReconciled += $reconCount;
                if ($reconCount > 0) {
                    $this->line("  → Reconciled: {$reconCount} customers assigned package_id");
                }
            }

            $this->newLine();
        }

        // Summary
        $this->info('============== RINGKASAN ==============');
        $this->line("Area gagal koneksi  : {$failedAreas}");
        $this->line("Packages created    : {$totalCreated}");
        $this->line("Packages updated    : {$totalUpdated}");
        $this->line("Packages skipped    : {$totalSkipped}");
        if ($reconcile) {
            $this->line("Customers reconciled: {$totalReconciled}");
        }
        $this->info('=======================================');

        if (!$apply) {
            $this->newLine();
            $this->warn('⚠ DRY-RUN — tidak ada perubahan di database.');
            $this->warn('Jalankan ulang dengan --apply untuk menerapkan:');
            $this->line('  php artisan pppoe:sync-profiles --apply --reconcile --skip-default');
        }

        return self::SUCCESS;
    }

    /**
     * Parse MikroTik rate-limit string to [download_mbps, upload_mbps].
     * Formats: "10M/5M", "10000000/5000000", "10M/10M 20M/20M ..." (queue), empty.
     */
    private function parseRateLimit(string $rateLimit): array
    {
        $rateLimit = trim($rateLimit);
        if ($rateLimit === '' || $rateLimit === '0/0') {
            return [0, 0];
        }

        // Take only the first part before any space (MikroTik queue uses "max/max burst/burst ...")
        $parts = explode(' ', $rateLimit);
        $main = $parts[0] ?? '';

        if (!str_contains($main, '/')) {
            return [0, 0];
        }

        [$rxPart, $txPart] = explode('/', $main, 2);

        $down = $this->parseSpeed($rxPart); // rx = download (from customer perspective via PPPoE)
        $up = $this->parseSpeed($txPart);   // tx = upload

        return [$down, $up];
    }

    /**
     * Parse speed value to Mbps integer.
     * "10M" → 10, "10000000" → 10, "512k" → 0 (< 1M rounded down), "1G" → 1000
     */
    private function parseSpeed(string $value): int
    {
        $value = trim(mb_strtolower($value));

        if ($value === '' || $value === '0') {
            return 0;
        }

        if (preg_match('/^(\d+(?:\.\d+)?)([kmg]?)$/', $value, $m)) {
            $num = (float) $m[1];
            $unit = $m[2];

            return match ($unit) {
                'g' => (int) ($num * 1000),
                'm' => (int) $num,
                'k' => (int) round($num / 1000),
                default => (int) round($num / 1_000_000), // raw bps
            };
        }

        // Fallback: try as raw number (bps)
        if (is_numeric($value)) {
            return (int) round((float) $value / 1_000_000);
        }

        return 0;
    }

    /**
     * Generate unique package code from profile name + area.
     */
    private function generateCode(string $profileName, Area $area): string
    {
        $areaPrefix = Str::upper(Str::substr(Str::slug($area->name), 0, 3));
        $profileSlug = Str::upper(Str::slug($profileName));
        $base = "{$areaPrefix}-{$profileSlug}";

        // Truncate to reasonable length
        $base = Str::substr($base, 0, 30);

        // Ensure uniqueness
        $code = $base;
        $counter = 1;
        while (Package::where('code', $code)->exists()) {
            $code = "{$base}-{$counter}";
            $counter++;
        }

        return $code;
    }

    /**
     * Reconcile: assign package_id to customers in this area that don't have one,
     * by matching their PPPoE secret's profile to a package.
     */
    private function reconcileArea(Area $area, bool $apply): int
    {
        // Get customers without package_id in this area
        $customers = Customer::where('area_id', $area->id)
            ->whereNull('package_id')
            ->whereNotNull('pppoe_user')
            ->where('pppoe_user', '!=', '')
            ->get();

        if ($customers->isEmpty()) {
            return 0;
        }

        // Get packages for this area indexed by normalized profile name
        $packages = Package::where('area_id', $area->id)
            ->whereNotNull('mikrotik_profile')
            ->where('mikrotik_profile', '!=', '')
            ->get()
            ->keyBy(fn($p) => mb_strtolower(trim($p->mikrotik_profile)));

        if ($packages->isEmpty()) {
            return 0;
        }

        // Connect to router to get secrets (we need their profile assignment)
        $mikrotik = MikroTikService::forArea($area);
        $secretsResult = $mikrotik->getAllSecrets();

        if (!($secretsResult['success'] ?? false)) {
            $this->warn("    Gagal ambil secrets untuk reconcile: " . ($secretsResult['error'] ?? 'Unknown'));
            return 0;
        }

        // Index secrets by normalized username
        $secretsByUser = collect($secretsResult['data'] ?? [])
            ->filter(fn($s) => !empty(trim($s['name'] ?? '')))
            ->keyBy(fn($s) => mb_strtolower(trim($s['name'])));

        $reconciled = 0;

        foreach ($customers as $customer) {
            $normUser = mb_strtolower(trim($customer->pppoe_user));
            $secret = $secretsByUser->get($normUser);

            if (!$secret) {
                continue;
            }

            $profileName = mb_strtolower(trim($secret['profile'] ?? ''));
            if ($profileName === '' || $profileName === 'default') {
                continue;
            }

            $package = $packages->get($profileName);
            if (!$package) {
                continue;
            }

            if ($apply) {
                $customer->update([
                    'package_id' => $package->id,
                    'package_price' => $package->price ?: $customer->package_price,
                ]);
            }
            $reconciled++;
        }

        return $reconciled;
    }
}
