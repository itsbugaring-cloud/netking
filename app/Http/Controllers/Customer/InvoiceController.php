<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    private function paymentSettings(): array
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

        return [
            'accounts' => $accounts,
            'qris' => $qris,
            'notes' => Setting::get(
                'manual_payment_notes',
                'Transfer atau bayar via QRIS sesuai nominal invoice, lalu upload bukti pembayaran agar admin bisa memverifikasi pembayaran Anda.'
            ),
        ];
    }

    /**
     * Display list of customer invoices
     */
    public function index()
    {
        $customer = auth('customer')->user();

        $invoices = $customer->invoices()
            ->latest()
            ->paginate(10);

        return view('customer.invoices.index', compact('invoices'));
    }

    /**
     * Display specific invoice details
     */
    public function show(Invoice $invoice)
    {
        $customer = auth('customer')->user();

        // Ensure customer can only view their own invoices
        if ($invoice->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to invoice');
        }

        $paymentSettings = $this->paymentSettings();

        return view('customer.invoices.show', compact('invoice', 'paymentSettings'));
    }

    public function submitPaymentProof(Request $request, Invoice $invoice)
    {
        $customer = auth('customer')->user();

        if ($invoice->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to invoice');
        }

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice sudah lunas.');
        }

        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'Invoice ini sudah dibatalkan.');
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
            'payment_reject_reason' => null,
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

        return back()->with('success', 'Bukti pembayaran berhasil dikirim. Admin akan meninjau pembayaran Anda.');
    }

    /**
     * Download invoice PDF
     */
    public function downloadPdf(Invoice $invoice)
    {
        $customer = auth('customer')->user();

        // Ensure customer can only download their own invoices
        if ($invoice->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to invoice');
        }

        // Generate PDF (reuse existing admin PDF logic)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.pdf', compact('invoice'));

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }
}
