<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Customer;
use App\Services\MikroTikService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AutoIsolateOverdue extends Command
{
    protected $signature = 'billing:auto-isolate
                            {--area= : Hanya proses satu area (ID)}
                            {--dry-run : Tampilkan siapa yang akan diisolir tanpa eksekusi}';

    protected $description = 'Isolir otomatis pelanggan yang belum membayar dan sudah melewati tanggal jatuh tempo.';

    public function handle(): int
    {
        $isDryRun  = $this->option('dry-run');
        $areaId    = $this->option('area');
        $listName  = config('netking.isolir_list', 'isolir');
        $today     = now()->day;
        $thisMonth = now()->month;
        $thisYear  = now()->year;

        if ($isDryRun) {
            $this->warn('[DRY-RUN] Tidak ada perubahan yang dilakukan.');
        }

        $areaQuery = Area::whereNotNull('router_ip')->where('router_ip', '!=', '');
        if ($areaId) {
            $areaQuery->where('id', (int) $areaId);
        }
        $areas = $areaQuery->get();

        if ($areas->isEmpty()) {
            $this->error('Tidak ada area dengan router yang ditemukan.');
            return self::FAILURE;
        }

        $totalIsolated = 0;
        $totalSkipped  = 0;
        $totalFailed   = 0;

        foreach ($areas as $area) {
            $this->line("\n<fg=cyan>Area: {$area->name}</>");

            // Cari pelanggan overdue:
            // - aktif, belum terisolir, punya remote IP
            // - tidak punya approved payment bulan ini
            // - hari ini >= billing_due_day pelanggan (atau default)
            $overdueCustomers = Customer::where('area_id', $area->id)
                ->where('status', 'active')
                ->where('is_free', false)
                ->where('is_isolated', false)
                ->whereNotNull('remote_ip')
                ->whereDoesntHave('payments', function ($q) use ($thisMonth, $thisYear) {
                    $q->where('status', 'approved')
                      ->where('periode_bulan', $thisMonth)
                      ->where('periode_tahun', $thisYear);
                })
                ->get()
                ->filter(function (Customer $customer) use ($today) {
                    // Isolir jika hari ini sudah lewat/sama dengan jatuh tempo pelanggan
                    $dueDay = $customer->billing_due_day ?? (int) config('billing.invoice_due_day', 20);
                    return $today > $dueDay;
                });

            if ($overdueCustomers->isEmpty()) {
                $this->line("  Tidak ada pelanggan overdue.");
                continue;
            }

            $this->info("  Ditemukan {$overdueCustomers->count()} pelanggan overdue.");

            if ($isDryRun) {
                foreach ($overdueCustomers as $c) {
                    $dueDay = $c->billing_due_day ?? (int) config('billing.invoice_due_day', 20);
                    $this->line("  [DRY-RUN] {$c->name} ({$c->customer_code}) — jatuh tempo tgl {$dueDay} — IP: {$c->remote_ip}");
                }
                $totalSkipped += $overdueCustomers->count();
                continue;
            }

            try {
                $mikrotik = MikroTikService::forArea($area);
            } catch (\Throwable $e) {
                $this->error("  Gagal connect MikroTik {$area->name}: {$e->getMessage()}");
                $totalFailed += $overdueCustomers->count();
                continue;
            }

            foreach ($overdueCustomers as $customer) {
                $check = $mikrotik->findInAddressList($customer->remote_ip, $listName);
                if ($check['found'] ?? false) {
                    // Sudah ada di address-list, sync DB saja
                    $customer->update(['is_isolated' => true, 'isolated_at' => now()]);
                    $totalSkipped++;
                    $this->line("  <fg=yellow>SKIP</> {$customer->name} — sudah di address-list.");
                    continue;
                }

                $comment = "nk-{$customer->id} {$customer->name}";
                $result  = $mikrotik->addToAddressList($customer->remote_ip, $listName, null, $comment);

                if ($result['success']) {
                    $customer->update(['is_isolated' => true, 'isolated_at' => now()]);
                    $totalIsolated++;
                    $this->line("  <fg=green>OK</> {$customer->name} ({$customer->customer_code}) — diisolir.");
                } else {
                    $totalFailed++;
                    $this->error("  GAGAL {$customer->name}: " . ($result['error'] ?? 'Unknown'));
                }

                usleep(300000); // 300ms delay antar request MikroTik
            }
        }

        $this->newLine();
        $this->info("Selesai: {$totalIsolated} diisolir, {$totalSkipped} skip, {$totalFailed} gagal.");

        return self::SUCCESS;
    }
}
