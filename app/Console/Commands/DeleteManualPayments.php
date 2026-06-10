<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;

class DeleteManualPayments extends Command
{
    protected $signature = 'payments:delete-manual
        {--month= : Filter periode_bulan (1-12)}
        {--year= : Filter periode_tahun (e.g. 2026)}
        {--created-date= : Filter by created_at date (YYYY-MM-DD)}
        {--customer= : Filter by customer name / PPPoE / code}
        {--apply : Actually delete matched rows}';

    protected $description = 'Bulk delete manual/admin-created payments. DRY-RUN by default.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $month = $this->option('month');
        $year = $this->option('year');
        $createdDate = trim((string) ($this->option('created-date') ?? ''));
        $customer = trim((string) ($this->option('customer') ?? ''));

        $query = Payment::query()
            ->with(['customer.area'])
            ->whereNotNull('created_by_user_id')
            ->where(function ($q) {
                $q->whereNull('bukti_path')->orWhere('bukti_path', '');
            });

        if ($month !== null && $month !== '') {
            $query->where('periode_bulan', (int) $month);
        }

        if ($year !== null && $year !== '') {
            $query->where('periode_tahun', (int) $year);
        }

        if ($createdDate !== '') {
            $query->whereDate('created_at', $createdDate);
        }

        if ($customer !== '') {
            $query->whereHas('customer', function ($q) use ($customer) {
                $q->where('name', 'like', '%' . $customer . '%')
                    ->orWhere('pppoe_user', 'like', '%' . $customer . '%')
                    ->orWhere('customer_code', 'like', '%' . $customer . '%');
            });
        }

        $payments = $query->orderByDesc('created_at')->get();

        $this->info('═══════════════════════════════════════════');
        $this->info('  Delete Manual Payments');
        $this->info('═══════════════════════════════════════════');
        $this->line('Mode: ' . ($apply ? '⚠️  APPLY (DELETE)' : '👁️  DRY-RUN (preview only)'));
        $this->line('Filters:'
            . ' month=' . ($month !== null && $month !== '' ? $month : '-')
            . ' year=' . ($year !== null && $year !== '' ? $year : '-')
            . ' created-date=' . ($createdDate !== '' ? $createdDate : '-')
            . ' customer=' . ($customer !== '' ? $customer : '-'));
        $this->newLine();

        if ($payments->isEmpty()) {
            $this->warn('Tidak ada pembayaran manual yang cocok dengan filter.');
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($payments->take(60) as $payment) {
            $rows[] = [
                $payment->id,
                $payment->customer?->customer_code ?? '-',
                $payment->customer?->pppoe_user ?? '-',
                $payment->customer?->name ?? '-',
                $payment->customer?->area?->name ?? '-',
                sprintf('%02d/%04d', (int) $payment->periode_bulan, (int) $payment->periode_tahun),
                number_format((float) $payment->jumlah, 0, ',', '.'),
                $payment->rekening_tujuan,
                $payment->created_at?->format('Y-m-d H:i') ?? '-',
            ];
        }

        $this->table(
            ['ID', 'Code', 'PPPoE', 'Customer', 'Area', 'Periode', 'Jumlah', 'Rekening', 'Created'],
            $rows
        );

        if ($payments->count() > 60) {
            $this->line('... and ' . ($payments->count() - 60) . ' more rows.');
        }

        $this->newLine();
        $this->line('Total matched manual payments: ' . $payments->count());

        if (!$apply) {
            $this->warn('DRY-RUN mode. Tambahkan --apply untuk benar-benar hapus.');
            return self::SUCCESS;
        }

        $deleted = 0;
        foreach ($payments as $payment) {
            $payment->delete();
            $deleted++;
        }

        $this->info("✓ Deleted {$deleted} manual payment(s).");

        return self::SUCCESS;
    }
}
