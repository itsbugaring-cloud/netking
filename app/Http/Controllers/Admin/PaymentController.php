<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
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

        if ($search) {
            $customer = \App\Models\Customer::where('customer_code', $search)
                ->orWhere('pppoe_user', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('phone', $search)
                ->with(['area', 'package'])
                ->first();
        }

        return view('admin.payments.quick', compact('customer', 'search'));
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
            'approved_at' => now(),
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
     * Bulk store payments for multiple customers at once.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'integer|exists:customers,id',
            'periode_bulan' => 'required|integer|min:1|max:12',
            'periode_tahun' => 'required|integer|min:2020|max:2030',
            'metode' => 'required|in:transfer,cash',
            'rekening_tujuan' => 'required|string|max:100',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $customers = Customer::with('package')
            ->whereIn('id', $validated['customer_ids'])
            ->get();

        $count = 0;
        foreach ($customers as $customer) {
            Payment::create([
                'customer_id' => $customer->id,
                'periode_bulan' => $validated['periode_bulan'],
                'periode_tahun' => $validated['periode_tahun'],
                'jumlah' => $customer->package->price ?? 0,
                'metode' => $validated['metode'],
                'rekening_tujuan' => $validated['rekening_tujuan'],
                'status' => 'approved',
                'approved_by_user_id' => auth()->id(),
                'approved_at' => now(),
                'catatan' => $validated['catatan'] ?? null,
                'created_by_user_id' => auth()->id(),
            ]);
            $count++;
        }

        return response()->json(['success' => true, 'count' => $count]);
    }
}
