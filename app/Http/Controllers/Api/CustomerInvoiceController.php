<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user();

        $invoices = $customer->invoices()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($invoices);
    }

    public function show(Request $request, $id)
    {
        $customer = $request->user();

        $invoice = $customer->invoices()
            ->find($id);

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        return response()->json(['data' => $invoice]);
    }

    public function submitPaymentProof(Request $request, Invoice $invoice)
    {
        $customer = $request->user();
        $invoice = $customer->invoices()->find($invoice->id);

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        if ($invoice->status === 'paid') {
            return response()->json(['message' => 'Invoice already paid'], 422);
        }

        if ($invoice->status === 'cancelled') {
            return response()->json(['message' => 'Invoice is cancelled'], 422);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string|max:50',
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($invoice->payment_proof_path) {
            Storage::disk('public')->delete($invoice->payment_proof_path);
        }

        $file = $request->file('payment_proof');
        $storedPath = $file->store("payment-proofs/customer-{$customer->id}", 'public');

        $invoice->update([
            'payment_method' => $validated['payment_method'],
            'payment_proof_path' => $storedPath,
            'payment_proof_original_name' => $file->getClientOriginalName(),
            'payment_proof_notes' => $validated['notes'] ?? null,
            'payment_proof_submitted_at' => now(),
            'payment_review_status' => 'submitted',
            'payment_reviewed_at' => null,
        ]);

        \App\Models\ActivityLog::log(
            'payment-proof-submitted',
            "Customer {$customer->name} submitted payment proof for invoice {$invoice->invoice_number}",
            $invoice,
            [
                'customer_id' => $customer->id,
                'invoice_number' => $invoice->invoice_number,
                'payment_method' => $validated['payment_method'],
            ]
        );

        \App\Models\AdminNotification::notify(
            'payment-proof',
            'Payment proof submitted',
            "{$customer->name} mengirim bukti bayar untuk invoice {$invoice->invoice_number}",
            'bx-receipt',
            'orange',
            route('admin.invoices.show', $invoice)
        );

        return response()->json([
            'message' => 'Bukti pembayaran berhasil dikirim. Admin akan meninjau pembayaran Anda.',
            'data' => $invoice->fresh(),
        ]);
    }

    public function paymentSettings()
    {
        $accounts = collect([
            [
                'bank_name' => Setting::get('payment_bank_1_name', 'BRI'),
                'account_number' => Setting::get('payment_bank_1_number', '159601000592564'),
                'account_holder' => Setting::get('payment_bank_1_holder', 'Deni Firmansyah'),
            ],
            [
                'bank_name' => Setting::get('payment_bank_2_name', 'BNI'),
                'account_number' => Setting::get('payment_bank_2_number', '0320906963'),
                'account_holder' => Setting::get('payment_bank_2_holder', 'Deni Firmansyah'),
            ],
        ])->filter(fn ($account) => filled($account['account_number']))->values();

        $defaultQrisUrl = url('/img/payments/QRIS-NETKING.jpg');
        $qrisImageUrl = Setting::get('payment_qris_image_url', $defaultQrisUrl);
        $qris = filled($qrisImageUrl)
            ? [
                'label' => Setting::get('payment_qris_label', 'QRIS NETKING'),
                'image_url' => $qrisImageUrl,
                'notes' => Setting::get(
                    'payment_qris_notes',
                    'Scan QRIS resmi NETKING, bayar sesuai nominal invoice, lalu unggah bukti pembayaran agar admin dapat memverifikasi pembayaran Anda.'
                ),
            ]
            : null;

        return response()->json([
            'data' => [
                'accounts' => $accounts,
                'qris' => $qris,
                'notes' => Setting::get(
                    'manual_payment_notes',
                    'Transfer sesuai nominal invoice, lalu upload bukti pembayaran agar admin bisa memverifikasi pembayaran Anda.'
                ),
            ],
        ]);
    }
}
