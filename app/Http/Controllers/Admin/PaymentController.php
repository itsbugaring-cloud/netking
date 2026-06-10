<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
     * Approve a pending payment.
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

        return back()->with('success', 'Pembayaran disetujui.');
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
            $customer = \App\Models\Customer::where('customer_code', $search)
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
        $validated = $request->validate([
            'periode_bulan' => 'required|integer|min:1|max:12',
            'periode_tahun' => 'required|integer|min:2020|max:2030',
            'jumlah' => 'required|numeric|min:0',
            'tanggal_bayar' => 'required|date',
            'metode' => 'required|in:transfer,cash',
            'rekening_tujuan' => 'required|string|max:50',
            'catatan' => 'nullable|string|max:1000',
        ]);

        Payment::create([
            'customer_id' => $customer->id,
            'periode_bulan' => $validated['periode_bulan'],
            'periode_tahun' => $validated['periode_tahun'],
            'jumlah' => $validated['jumlah'],
            'metode' => $validated['metode'],
            'rekening_tujuan' => $validated['rekening_tujuan'],
            'status' => 'approved',
            'approved_by_user_id' => auth()->id(),
            'approved_at' => Carbon::parse($validated['tanggal_bayar'])->startOfDay(),
            'catatan' => $validated['catatan'] ?? null,
            'created_by_user_id' => auth()->id(),
        ]);

        // If came from quick payment page, redirect back there
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
            'approved_at' => Carbon::parse($validated['tanggal_bayar'])->startOfDay(),
            'approved_by_user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Tanggal bayar pembayaran manual berhasil diperbarui.');
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

        $customer = $payment->customer;
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
            'payment_ids' => 'required|array|min:1',
            'payment_ids.*' => 'integer|exists:payments,id',
            'customer_id' => 'nullable|integer|exists:customers,id',
        ]);

        $payments = Payment::whereIn('id', $validated['payment_ids'])
            ->get();

        $manualPayments = $payments->filter(fn (Payment $payment) => $payment->created_by_user_id !== null && empty($payment->bukti_path));

        if ($manualPayments->isEmpty()) {
            return back()->with('error', 'Tidak ada pembayaran manual yang valid untuk dihapus.');
        }

        if ($manualPayments->count() !== $payments->count()) {
            return back()->with('error', 'Sebagian data yang dipilih bukan pembayaran manual, jadi penghapusan dibatalkan.');
        }

        $customerId = (int) ($validated['customer_id'] ?? 0);
        if ($customerId > 0 && $manualPayments->contains(fn (Payment $payment) => (int) $payment->customer_id !== $customerId)) {
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


}
