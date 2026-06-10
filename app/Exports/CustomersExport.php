<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export customers to CSV (Excel-compatible, no external library needed).
 * Opens correctly in Excel with UTF-8 BOM.
 */
class CustomersExport
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function download(string $filename): StreamedResponse
    {
        $query = Customer::with(['partner', 'area', 'package'])
            ->with(['latestPayment' => function ($q) {
                $q->with('approvedBy:id,name');
            }]);

        if ($this->request->filled('area_id')) {
            $query->where('area_id', $this->request->area_id);
        }
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        $customers = $query->orderBy('area_id')->orderBy('name')->get();

        return response()->streamDownload(function () use ($customers) {
            $out = fopen('php://output', 'w');

            // BOM for Excel UTF-8 recognition
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($out, [
                'No',
                'PIC',
                'Area',
                'Nama',
                'No. HP',
                'Layanan',
                'Bayar (Rp)',
                'Status',
                'Tgl Berlangganan',
                'Tgl Bayar',
                'Pembayaran',
                'Rekening',
                'Approved by',
                'Keterangan',
            ]);

            $statusLabels = [
                'active' => 'Aktif',
                'suspended' => 'Diisolir',
                'inactive' => 'Nonaktif',
            ];

            foreach ($customers as $i => $c) {
                $latestPayment = $c->latestPayment;
                $metode = match ($latestPayment?->metode) {
                    'transfer' => 'Transfer',
                    'cash' => 'Tunai',
                    default => $latestPayment?->metode ? ucfirst($latestPayment->metode) : '',
                };

                fputcsv($out, [
                    $i + 1,
                    $c->partner?->name ?? '',
                    $c->area?->name ?? '-',
                    $c->name,
                    $c->phone ?? '',
                    $c->package?->name ?? '-',
                    (int) ($c->package_price ?: ($c->package?->price ?? 0)),
                    $statusLabels[$c->status] ?? ucfirst($c->status),
                    $c->billing_start_date?->format('d/m/Y') ?? '',
                    $latestPayment?->approved_at?->format('d/m/Y') ?? '',
                    $metode,
                    $latestPayment?->rekening_tujuan ?? '',
                    $latestPayment?->approvedBy?->name ?? '',
                    $latestPayment?->catatan ?? '',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
