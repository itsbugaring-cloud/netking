<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use App\Services\MikroTikService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ReconcilePppoeDbWithMikrotik extends Command
{
    protected $signature = 'pppoe:reconcile
        {--area_id= : Sync hanya untuk 1 area ID}
        {--apply : Terapkan perubahan ke database (default: dry-run)}
        {--sync-password : Ikut update pppoe_pass dari secret MikroTik}
        {--limit-details=25 : Maksimal detail username mismatch yang ditampilkan per area}';

    protected $description = 'Rekonsiliasi data PPPoE DB customer vs secret live MikroTik per area (aman, tanpa write ke MikroTik)';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $syncPassword = (bool) $this->option('sync-password');
        $limitDetails = max(1, (int) $this->option('limit-details'));
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
        $this->info('  PPPoE Reconcile DB vs MikroTik');
        $this->info('==============================================');
        $this->line('Mode: ' . ($apply ? 'APPLY (tulis ke DB)' : 'DRY-RUN (tanpa perubahan DB)'));
        $this->line('Sync password: ' . ($syncPassword ? 'YA' : 'TIDAK'));
        $this->newLine();

        $grand = [
            'areas_ok' => 0,
            'areas_failed' => 0,
            'router_total' => 0,
            'db_total' => 0,
            'missing_in_db' => 0,
            'missing_in_router' => 0,
            'updated' => 0,
            'created' => 0,
        ];

        foreach ($areas as $area) {
            $this->line("Area #{$area->id} - {$area->name} ({$area->router_ip})");

            $mikrotik = MikroTikService::forArea($area);
            $test = $mikrotik->testConnection();
            if (!($test['success'] ?? false)) {
                $grand['areas_failed']++;
                $this->error('  x Gagal konek router: ' . ($test['error'] ?? 'Unknown error'));
                $this->newLine();
                continue;
            }

            $secretsResult = $mikrotik->getAllSecrets();
            if (!($secretsResult['success'] ?? false)) {
                $grand['areas_failed']++;
                $this->error('  x Gagal ambil secret: ' . ($secretsResult['error'] ?? 'Unknown error'));
                $this->newLine();
                continue;
            }

            $secrets = collect($secretsResult['data'] ?? [])
                ->filter(fn ($row) => !empty(trim((string) ($row['name'] ?? ''))))
                ->values();

            $secretByUser = $secrets->keyBy(fn ($row) => $this->norm((string) $row['name']));

            $customers = Customer::query()
                ->where('area_id', $area->id)
                ->whereNotNull('pppoe_user')
                ->where('pppoe_user', '!=', '')
                ->get();

            $customerByUser = $customers->keyBy(fn ($c) => $this->norm((string) $c->pppoe_user));

            $packagesByProfile = Package::query()
                ->where('area_id', $area->id)
                ->whereNotNull('mikrotik_profile')
                ->where('mikrotik_profile', '!=', '')
                ->get()
                ->keyBy(fn ($p) => $this->norm((string) $p->mikrotik_profile));

            $routerUsers = $secretByUser->keys();
            $dbUsers = $customerByUser->keys();

            $missingInDb = $routerUsers->diff($dbUsers)->values();
            $missingInRouter = $dbUsers->diff($routerUsers)->values();

            $updated = 0;
            $created = 0;
            $changesPreview = [];

            if ($apply) {
                [$created, $updated, $changesPreview] = $this->applySync(
                    $area->id,
                    $secretByUser,
                    $customerByUser,
                    $packagesByProfile,
                    $missingInDb,
                    $syncPassword,
                    $limitDetails
                );
            } else {
                [$potentialUpdates, $changesPreview] = $this->collectPotentialChanges(
                    $secretByUser,
                    $customerByUser,
                    $packagesByProfile,
                    $syncPassword,
                    $limitDetails
                );
                $created = $missingInDb->count();
                $updated = $potentialUpdates;
            }

            $grand['areas_ok']++;
            $grand['router_total'] += $routerUsers->count();
            $grand['db_total'] += $dbUsers->count();
            $grand['missing_in_db'] += $missingInDb->count();
            $grand['missing_in_router'] += $missingInRouter->count();
            $grand['updated'] += $updated;
            $grand['created'] += $created;

            $this->line("  Router secrets: {$routerUsers->count()}");
            $this->line("  DB customers : {$dbUsers->count()}");
            $this->line("  Missing in DB: {$missingInDb->count()}");
            $this->line("  Missing in Router: {$missingInRouter->count()}");
            $this->line("  " . ($apply ? 'Created' : 'Will create') . ": {$created}");
            $this->line("  " . ($apply ? 'Updated' : 'Will update') . ": {$updated}");

            if ($missingInDb->isNotEmpty()) {
                $sample = $missingInDb->take($limitDetails)->map(fn ($u) => $secretByUser->get($u)['name'] ?? $u)->implode(', ');
                $suffix = $missingInDb->count() > $limitDetails ? ' ...' : '';
                $this->line("  + Router->DB sample: {$sample}{$suffix}");
            }

            if ($missingInRouter->isNotEmpty()) {
                $sample = $missingInRouter->take($limitDetails)->map(fn ($u) => $customerByUser->get($u)->pppoe_user ?? $u)->implode(', ');
                $suffix = $missingInRouter->count() > $limitDetails ? ' ...' : '';
                $this->line("  - DB only sample: {$sample}{$suffix}");
            }

            if (!empty($changesPreview)) {
                $this->line('  * Update preview:');
                foreach ($changesPreview as $line) {
                    $this->line("    - {$line}");
                }
            }

            $this->newLine();
        }

        $this->info('============== RINGKASAN ==============');
        $this->line("Area sukses         : {$grand['areas_ok']}");
        $this->line("Area gagal koneksi  : {$grand['areas_failed']}");
        $this->line("Total secret router : {$grand['router_total']}");
        $this->line("Total customer DB   : {$grand['db_total']}");
        $this->line("Missing in DB       : {$grand['missing_in_db']}");
        $this->line("Missing in Router   : {$grand['missing_in_router']}");
        $this->line(($apply ? 'Created' : 'Will create') . "       : {$grand['created']}");
        $this->line(($apply ? 'Updated' : 'Will update') . "       : {$grand['updated']}");
        $this->info('=======================================');

        if (!$apply) {
            $this->warn('Ini DRY-RUN. Jalankan ulang pakai --apply jika hasilnya sudah sesuai.');
        }

        return self::SUCCESS;
    }

    private function applySync(
        int $areaId,
        Collection $secretByUser,
        Collection $customerByUser,
        Collection $packagesByProfile,
        Collection $missingInDb,
        bool $syncPassword,
        int $limitDetails
    ): array {
        $created = 0;
        $updated = 0;
        $preview = [];

        $areaPartners = User::query()
            ->where('role', 'partner')
            ->where('area_id', $areaId)
            ->orderBy('id')
            ->get(['id', 'name']);
        $partnerId = $areaPartners->count() === 1 ? (int) $areaPartners->first()->id : null;

        foreach ($missingInDb as $normUser) {
            $secret = $secretByUser->get($normUser);
            if (!$secret) {
                continue;
            }

            $profileNorm = $this->norm((string) ($secret['profile'] ?? ''));
            $package = $packagesByProfile->get($profileNorm);

            Customer::create([
                'name' => trim((string) ($secret['comment'] ?? '')) ?: (string) ($secret['name'] ?? ''),
                'pppoe_user' => (string) ($secret['name'] ?? ''),
                'pppoe_pass' => (string) ($secret['password'] ?? Str::random(12)),
                'portal_password' => Hash::make(Str::random(10)),
                'area_id' => $areaId,
                'partner_id' => $partnerId,
                'package_id' => $package?->id,
                'package_price' => $package?->price ?? 0,
                'status' => 'active',
            ]);
            $created++;
        }

        foreach ($customerByUser as $normUser => $customer) {
            $secret = $secretByUser->get($normUser);
            if (!$secret) {
                continue;
            }

            $changes = [];
            $secretComment = trim((string) ($secret['comment'] ?? ''));
            $secretPassword = (string) ($secret['password'] ?? '');
            $profileNorm = $this->norm((string) ($secret['profile'] ?? ''));
            $package = $packagesByProfile->get($profileNorm);

            if ($secretComment !== '' && $customer->name !== $secretComment) {
                $changes['name'] = $secretComment;
            }

            if ($package && (int) $customer->package_id !== (int) $package->id) {
                $changes['package_id'] = (int) $package->id;
                $changes['package_price'] = $package->price;
            }

            if ($syncPassword && $secretPassword !== '' && $customer->pppoe_pass !== $secretPassword) {
                $changes['pppoe_pass'] = $secretPassword;
            }

            if (!empty($changes)) {
                $customer->update($changes);
                $updated++;
                if (count($preview) < $limitDetails) {
                    $preview[] = ($customer->pppoe_user ?: $customer->id) . ' [' . implode(', ', array_keys($changes)) . ']';
                }
            }
        }

        return [$created, $updated, $preview];
    }

    private function collectPotentialChanges(
        Collection $secretByUser,
        Collection $customerByUser,
        Collection $packagesByProfile,
        bool $syncPassword,
        int $limitDetails
    ): array {
        $preview = [];
        $count = 0;
        foreach ($customerByUser as $normUser => $customer) {
            $secret = $secretByUser->get($normUser);
            if (!$secret) {
                continue;
            }

            $fields = [];
            $secretComment = trim((string) ($secret['comment'] ?? ''));
            if ($secretComment !== '' && $customer->name !== $secretComment) {
                $fields[] = 'name';
            }

            $profileNorm = $this->norm((string) ($secret['profile'] ?? ''));
            $package = $packagesByProfile->get($profileNorm);
            if ($package && (int) $customer->package_id !== (int) $package->id) {
                $fields[] = 'package';
            }

            $secretPassword = (string) ($secret['password'] ?? '');
            if ($syncPassword && $secretPassword !== '' && $customer->pppoe_pass !== $secretPassword) {
                $fields[] = 'pppoe_pass';
            }

            if (!empty($fields)) {
                $count++;
                if (count($preview) < $limitDetails) {
                    $preview[] = ($customer->pppoe_user ?: $customer->id) . ' [' . implode(', ', $fields) . ']';
                }
            }
        }

        return [$count, $preview];
    }

    private function norm(string $value): string
    {
        return mb_strtolower(trim($value));
    }
}
