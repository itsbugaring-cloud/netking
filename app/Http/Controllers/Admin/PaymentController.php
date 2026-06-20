<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Payment;
use App\Services\MikroTikService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Display list of pending payments for review.
     */
    public function reviewIndex()
    {
        $payments = Payment::with(['customer.partner', 'customer.area', 'customer.package'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.payments.review', compact('payments'));
    }

    /**
     * Approve a pending payment + auto de-isolir MikroTik jika pelanggan terisolir.
     */
    public function approve(Request $request, Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Pembayaran ini sudah tidak dalam status menunggu review.');
        }

        $validated = $request->validate([
            'periode_bulan' => 'nullable|integer|min:1|max:12',
            'periode_tahun' => 'nullable|integer|min:2020|max:2030',
        ]);

        $payment->approve(
            auth()->id(),
            $validated['periode_bulan'] ?? null,
            $validated['periode_tahun'] ?? null
        );

        $customer = $payment->customer->load('area');
        $deisolate = $this->tryDeisolate($customer);

        if (!$deisolate['success']) {
            return back()->with('warning', 'Pembayaran disetujui, namun de-isolir MikroTik gagal: ' . $deisolate['error'] . '. Lakukan de-isolir manual.');
        }

        $msg = $deisolate['deisolated'] ? 'Pembayaran disetujui & isolasi dicabut.' : 'Pembayaran disetujui.';
        return back()->with('success', $msg);
    }

    /**
     * Bulk approve multiple pending payments + auto de-isolir per customer.
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'payment_ids'   => 'required|array|min:1',
            'payment_ids.*' => 'integer|exists:payments,id',
        ]);

        $payments = Payment::with(['customer.area'])
            ->whereIn('id', $validated['payment_ids'])
            ->where('status', 'pending')
            ->get();

        if ($payments->isEmpty()) {
            return back()->with('error', 'Tidak ada pembayaran pending yang valid untuk disetujui.');
        }

        $approved       = 0;
        $failedDeisolate = [];

        foreach ($payments as $payment) {
            $payment->approve(auth()->id());
            $approved++;

            $result = $this->tryDeisolate($payment->customer);
            if (!$result['success']) {
                $failedDeisolate[] = $payment->customer->name ?? "ID #{$payment->customer_id}";
            }
        }

        if ($failedDeisolate) {
            return back()->with('warning',
                "{$approved} pembayaran disetujui. De-isolir MikroTik gagal untuk: " .
                implode(', ', $failedDeisolate) . ". Lakukan de-isolir manual."
            );
        }

        return back()->with('success', "{$approved} pembayaran disetujui" . ($approved > 0 ? ' & isolasi dicabut.' : '.'));
    }

    /**
     * Reject a pending payment.
     */
    public function reject(Request $request, Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Pembayaran ini sudah tidak dalam status menunggu review.');
        }

        $validated = $request->validate([
            'reject_reason' => 'required|string|max:500',
        ]);

        $payment->reject($validated['reject_reason']);

        return back()->with('success', 'Pembayaran ditolak.');
    }

    /**
     * Quick payment page — search customer and pay in one page.
     */
    public function quickPayment(Request $request)
    {
        $search = $request->input('q');
        $customer = null;
        $manualMonth = (int) ($request->input('manual_month', now()->month));
        $manualYear = (int) ($request->input('manual_year', now()->year));

        if ($search) {
            $customer = Customer::where('customer_code', $search)
                ->orWhere('pppoe_user', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('phone', $search)
                ->with(['area', 'package'])
                ->first();
        }

        $manualPayments = Payment::with(['customer.area'])
            ->whereNotNull('created_by_user_id')
            ->where(function ($q) {
                $q->whereNull('bukti_path')->orWhere('bukti_path', '');
            })
            ->where('periode_bulan', $manualMonth)
            ->where('periode_tahun', $manualYear)
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return view('admin.payments.quick', compact('customer', 'search', 'manualPayments', 'manualMonth', 'manualYear'));
    }

    /**
     * Show manual payment form for a customer.
     */
    public function manualPaymentForm(Customer $customer)
    {
        $customer->load(['area', 'package', 'partner']);

        return view('admin.payments.manual', compact('customer'));
    }

    /**
     * Store a manual payment (immediately approved).
     */
    public function manualPaymentStore(Request $request, Customer $customer)
    {
        if ($customer->is_free) {
            return back()->with('error', 'Pelanggan gratis tidak perlu dicatat pembayaran manual.');
        }

        $validated = $request->validate([
            'periode_bulan'   => 'required|integer|min:1|max:12',
            'periode_tahun'   => 'required|integer|min:2020|max:2030',
            'jumlah'          => 'required|numeric|min:0',
            'tanggal_bayar'   => 'required|date',
            'metode'          => 'required|in:transfer,cash',
            'rekening_tujuan' => 'required|string|max:50',
            'catatan'         => 'nullable|string|max:1000',
        ]);

        Payment::create([
            'customer_id'       => $customer->id,
            'periode_bulan'     => $validated['periode_bulan'],
            'periode_tahun'     => $validated['periode_tahun'],
            'jumlah'            => $validated['jumlah'],
            'metode'            => $validated['metode'],
            'rekening_tujuan'   => $validated['rekening_tujuan'],
            'status'            => 'approved',
            'approved_by_user_id' => auth()->id(),
            'approved_at'       => Carbon::parse($validated['tanggal_bayar'])->startOfDay(),
            'catatan'           => $validated['catatan'] ?? null,
            'created_by_user_id' => auth()->id(),
        ]);

        $referer = $request->headers->get('referer', '');
        if (str_contains($referer, 'payments/quick')) {
            return redirect()->route('admin.payments.quick')
                ->with('success', 'Pembayaran manual berhasil dicatat untuk ' . $customer->name . '.');
        }

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Pembayaran manual berhasil dicatat.');
    }

    /**
     * Update transfer/payment date for a manual/admin-created payment entry.
     */
    public function updateManualDate(Request $request, Payment $payment)
    {
        $isManualEntry = $payment->created_by_user_id !== null && empty($payment->bukti_path);

        if (!$isManualEntry) {
            return back()->with('error', 'Hanya pembayaran manual yang bisa diubah tanggal bayarnya.');
        }

        $validated = $request->validate([
            'tanggal_bayar' => 'required|date',
        ]);

        $payment->update([
            'approved_at'        => Carbon::parse($validated['tanggal_bayar'])->startOfDay(),
            'approved_by_user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Tanggal bayar pembayaran manual berhasil diperbarui.');
    }

    /**
     * Bulk update transfer/payment dates for manual/admin-created payment entries.
     */
    public function bulkUpdateManualDates(Request $request)
    {
        $validated = $request->validate([
            'payment_dates'   => 'required|array|min:1',
            'payment_dates.*' => 'nullable|date',
        ]);

        $paymentIds = array_keys($validated['payment_dates']);
        $payments   = Payment::whereIn('id', $paymentIds)->get()->keyBy('id');

        $updated = 0;
        foreach ($validated['payment_dates'] as $paymentId => $tanggalBayar) {
            if (!$tanggalBayar) continue;

            $payment = $payments->get((int) $paymentId);
            if (!$payment) continue;

            $isManualEntry = $payment->created_by_user_id !== null && empty($payment->bukti_path);
            if (!$isManualEntry) continue;

            $payment->update([
                'approved_at'        => Carbon::parse($tanggalBayar)->startOfDay(),
                'approved_by_user_id' => auth()->id(),
            ]);
            $updated++;
        }

        return back()->with('success', $updated > 0
            ? $updated . ' tanggal bayar pembayaran manual berhasil diperbarui.'
            : 'Tidak ada tanggal bayar yang diubah.');
    }

    /**
     * Delete a manual/admin-created payment entry.
     */
    public function destroy(Payment $payment)
    {
        $isManualEntry = $payment->created_by_user_id !== null && empty($payment->bukti_path);

        if (!$isManualEntry) {
            return back()->with('error', 'Hanya pembayaran manual yang bisa dihapus dari sini.');
        }

        $customer    = $payment->customer;
        $periodLabel = sprintf('%02d/%s', (int) $payment->periode_bulan, (string) $payment->periode_tahun);

        $payment->delete();

        if ($customer) {
            return redirect()->route('admin.customers.show', $customer)
                ->with('success', 'Pembayaran manual periode ' . $periodLabel . ' berhasil dihapus.');
        }

        return redirect()->route('admin.payments.quick')
            ->with('success', 'Pembayaran manual periode ' . $periodLabel . ' berhasil dihapus.');
    }

    /**
     * Bulk delete manual/admin-created payment entries.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'payment_ids'   => 'required|array|min:1',
            'payment_ids.*' => 'integer|exists:payments,id',
            'customer_id'   => 'nullable|integer|exists:customers,id',
        ]);

        $payments = Payment::whereIn('id', $validated['payment_ids'])->get();

        $manualPayments = $payments->filter(
            fn (Payment $p) => $p->created_by_user_id !== null && empty($p->bukti_path)
        );

        if ($manualPayments->isEmpty()) {
            return back()->with('error', 'Tidak ada pembayaran manual yang valid untuk dihapus.');
        }

        if ($manualPayments->count() !== $payments->count()) {
            return back()->with('error', 'Sebagian data yang dipilih bukan pembayaran manual, jadi penghapusan dibatalkan.');
        }

        $customerId = (int) ($validated['customer_id'] ?? 0);
        if ($customerId > 0 && $manualPayments->contains(fn (Payment $p) => (int) $p->customer_id !== $customerId)) {
            return back()->with('error', 'Ada pembayaran dari pelanggan lain di pilihan Anda. Penghapusan dibatalkan.');
        }

        $deletedCount = $manualPayments->count();
        $manualPayments->each->delete();

        if ($customerId > 0) {
            return redirect()->route('admin.customers.show', $customerId)
                ->with('success', $deletedCount . ' pembayaran manual berhasil dihapus.');
        }

        return redirect()->route('admin.payments.quick')
            ->with('success', $deletedCount . ' pembayaran manual berhasil dihapus.');
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    /**
     * Coba de-isolir customer dari MikroTik address-list.
     * Return ['success' => bool, 'deisolated' => bool, 'error' => string|null]
     */
    private function tryDeisolate(Customer $customer): array
    {
        if (!$customer->is_isolated || !$customer->remote_ip) {
            return ['success' => true, 'deisolated' => false, 'error' => null];
        }

        $area = $customer->area ?? Area::find($customer->area_id);
        if (!$area || !$area->router_ip) {
            // Tidak ada router — update DB saja
            $customer->update(['is_isolated' => false, 'isolated_at' => null]);
            return ['success' => true, 'deisolated' => true, 'error' => null];
        }

        try {
            $mikrotik = MikroTikService::forArea($area);
            $listName = config('netking.isolir_list', 'isolir');
            $check    = $mikrotik->findInAddressList($customer->remote_ip, $listName);

            if (!($check['found'] ?? false)) {
                // Tidak ada di address-list, sync DB saja
                $customer->update(['is_isolated' => false, 'isolated_at' => null]);
                return ['success' => true, 'deisolated' => true, 'error' => null];
            }

            $entryId = $check['data'][0]['.id'] ?? null;
            if (!$entryId) {
                return ['success' => false, 'deisolated' => false, 'error' => 'Entry ID tidak ditemukan di address-list.'];
            }

            $result = $mikrotik->removeFromAddressList($entryId);
            if ($result['success']) {
                $customer->update(['is_isolated' => false, 'isolated_at' => null]);
                return ['success' => true, 'deisolated' => true, 'error' => null];
            }

            return ['success' => false, 'deisolated' => false, 'error' => $result['error'] ?? 'Unknown MikroTik error.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'deisolated' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Import bulk payments from Excel file
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file'          => 'required|file|max:10240',
            'periode_bulan' => 'required|integer|min:1|max:12',
            'periode_tahun' => 'required|integer|min:2020|max:2030',
        ]);

        $file = $request->file('file');
        $bulan = $request->input('periode_bulan');
        $tahun = $request->input('periode_tahun');

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) <= 1) {
                return back()->with('error', 'File kosong atau tidak ada data baris.');
            }

            $successCount = 0;
            $skippedCount = 0;
            $failedCount = 0;

            // Header mapping check (opsional, tapi kita asumsi format fix sesuai export)
            // Index 1 = ID Pelanggan
            // Index 7 = Bayar (Rp)
            // Index 10 = Tgl Bayar
            // Index 11 = Pembayaran (Metode / Lunas)
            // Index 12 = Rekening
            // Index 14 = Keterangan

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip header

                $customerCode = trim($row[1] ?? '');
                if (empty($customerCode)) continue;

                $tglBayarRaw = trim($row[10] ?? '');
                $pembayaranRaw = trim($row[11] ?? '');

                $isPaid = false;
                if (!empty($tglBayarRaw) || in_array(strtolower($pembayaranRaw), ['lunas', 'y', 'transfer', 'cash', 'tunai'])) {
                    $isPaid = true;
                }

                if (!$isPaid) {
                    continue; // Skip pelanggan yang tidak terindikasi bayar
                }

                $customer = Customer::where('customer_code', $customerCode)->with('area')->first();
                if (!$customer) {
                    $failedCount++;
                    continue;
                }

                // Cek apakah sudah ada payment LUNAS di bulan & tahun ini
                $existingPaid = Payment::where('customer_id', $customer->id)
                    ->where('periode_bulan', $bulan)
                    ->where('periode_tahun', $tahun)
                    ->where('status', 'approved')
                    ->exists();

                if ($existingPaid) {
                    $skippedCount++;
                    continue;
                }

                $bayarRp = (float) str_replace(['Rp', '.', ',', ' '], '', trim($row[7] ?? ''));
                if ($bayarRp <= 0) {
                    $bayarRp = $customer->package->price ?? 0;
                }

                $metode = in_array(strtolower($pembayaranRaw), ['cash', 'tunai']) ? 'cash' : 'transfer';
                $rekening = trim($row[12] ?? '') ?: 'Cash/Transfer';
                $keterangan = trim($row[14] ?? '') ?: 'Import Otomatis';

                // Cari apakah ada yang pending di bulan tsb, kalau ada update, kalau tidak buat baru
                $payment = Payment::where('customer_id', $customer->id)
                    ->where('periode_bulan', $bulan)
                    ->where('periode_tahun', $tahun)
                    ->where('status', 'pending')
                    ->first();

                if ($payment) {
                    $payment->update([
                        'status'              => 'approved',
                        'jumlah'              => $bayarRp,
                        'metode'              => $metode,
                        'rekening_tujuan'     => $rekening,
                        'catatan'             => $keterangan,
                        'approved_by_user_id' => auth()->id(),
                        'approved_at'         => now(),
                    ]);
                } else {
                    Payment::create([
                        'customer_id'         => $customer->id,
                        'periode_bulan'       => $bulan,
                        'periode_tahun'       => $tahun,
                        'jumlah'              => $bayarRp,
                        'metode'              => $metode,
                        'rekening_tujuan'     => $rekening,
                        'status'              => 'approved',
                        'approved_by_user_id' => auth()->id(),
                        'approved_at'         => now(),
                        'catatan'             => $keterangan,
                        'created_by_user_id'  => auth()->id(),
                    ]);
                }

                // Lepas Isolir
                $deisolate = $this->tryDeisolate($customer);
                if ($customer->status === 'suspended') {
                    $customer->update(['status' => 'active']);
                }

                // Update billing_start_date jika kolom Tgl Berlangganan diisi
                $tglBerlanggananRaw = trim($row[9] ?? '');
                if (!empty($tglBerlanggananRaw)) {
                    try {
                        $parsedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $tglBerlanggananRaw);
                        if ($parsedDate && $parsedDate->toDateString() !== optional($customer->billing_start_date)->toDateString()) {
                            $customer->update(['billing_start_date' => $parsedDate->toDateString()]);
                        }
                    } catch (\Throwable $e) {
                        // Try other date formats
                        try {
                            $parsedDate = \Carbon\Carbon::parse($tglBerlanggananRaw);
                            if ($parsedDate && $parsedDate->toDateString() !== optional($customer->billing_start_date)->toDateString()) {
                                $customer->update(['billing_start_date' => $parsedDate->toDateString()]);
                            }
                        } catch (\Throwable $e2) {
                            // Invalid date format — skip
                        }
                    }
                }

                // Update package (Layanan) jika kolom diisi dan berbeda
                $layananRaw = trim($row[6] ?? '');
                if (!empty($layananRaw)) {
                    $package = \App\Models\Package::where('name', $layananRaw)
                        ->where('area_id', $customer->area_id)
                        ->where('is_active', true)
                        ->first();
                    if ($package && $package->id !== $customer->package_id) {
                        $customer->update([
                            'package_id' => $package->id,
                            'package_price' => $package->price,
                        ]);
                    }
                }

                $successCount++;
            }

            return back()->with('success', "Import selesai: $successCount Lunas, $skippedCount Di-skip (Sudah Lunas), $failedCount Gagal (ID tidak valid).");

        } catch (\Throwable $e) {
            Log::error('Error Import Pembayaran Excel: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }
}
