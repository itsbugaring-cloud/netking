<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentPageController extends Controller
{
    public function show(Request $request, ?string $customerCode = null)
    {
        $customerCode = strtoupper(trim((string) ($customerCode ?: $request->query('customer_code', ''))));

        $customer = null;
        $invoices = collect();
        $selectedInvoice = null;

        if ($customerCode !== '') {
            $customer = Customer::query()
                ->with(['package', 'area'])
                ->whereRaw('UPPER(TRIM(customer_code)) = ?', [$customerCode])
                ->first();

            if (!$customer) {
                return view('payments.public', [
                    'customerCode' => $customerCode,
                    'customer' => null,
                    'invoices' => collect(),
                    'selectedInvoice' => null,
                    'paymentSettings' => $this->paymentSettings(),
                ])->with('error', 'ID pelanggan tidak ditemukan.');
            }

            $invoices = $customer->invoices()
                ->where('status', 'unpaid')
                ->orderBy('due_date')
                ->get();

            $selectedInvoiceId = (int) $request->query('invoice', 0);
            $selectedInvoice = $selectedInvoiceId > 0
                ? $invoices->firstWhere('id', $selectedInvoiceId)
                : ($invoices->count() === 1 ? $invoices->first() : null);
        }

        return view('payments.public', [
            'customerCode' => $customerCode,
            'customer' => $customer,
            'invoices' => $invoices,
            'selectedInvoice' => $selectedInvoice,
            'paymentSettings' => $this->paymentSettings(),
        ]);
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'customer_code' => 'required|string|max:32',
            'invoice_id' => 'required|integer',
            'payment_method' => 'required|string|max:50',
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $customerCode = strtoupper(trim((string) $validated['customer_code']));
        $customer = Customer::query()
            ->whereRaw('UPPER(TRIM(customer_code)) = ?', [$customerCode])
            ->first();

        if (!$customer) {
            return back()->withInput()->with('error', 'ID pelanggan tidak ditemukan.');
        }

        $invoice = Invoice::query()
            ->where('id', $validated['invoice_id'])
            ->where('customer_id', $customer->id)
            ->first();

        if (!$invoice) {
            return back()->withInput()->with('error', 'Tagihan tidak ditemukan untuk ID pelanggan tersebut.');
        }

        if ($invoice->status === 'paid') {
            return back()->withInput()->with('error', 'Tagihan ini sudah lunas.');
        }

        if ($invoice->status === 'cancelled') {
            return back()->withInput()->with('error', 'Tagihan ini sudah dibatalkan.');
        }

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

        // Auto-approve: langsung tandai lunas + re-enable PPPoE
        $invoice->markAsPaid($validated['payment_method']);

        \App\Models\ActivityLog::log(
            'payment-auto-approved',
            "Customer {$customer->name} uploaded payment proof and invoice {$invoice->invoice_number} auto-approved",
            $invoice,
            [
                'customer_id' => $customer->id,
                'invoice_number' => $invoice->invoice_number,
                'payment_method' => $validated['payment_method'],
            ]
        );

        AdminNotification::notify(
            'payment-proof',
            'Payment auto-approved',
            "{$customer->name} bayar invoice {$invoice->invoice_number} — otomatis lunas",
            'bx-check-circle',
            'green',
            route('admin.invoices.show', $invoice)
        );

        return redirect()
            ->route('payment.public', ['customerCode' => $customerCode, 'invoice' => $invoice->id])
            ->with('success', 'Pembayaran berhasil! Tagihan Anda sudah lunas.');
    }

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
            [
                'bank_name' => Setting::get('payment_bank_3_name', 'MANDIRI'),
                'account_number' => Setting::get('payment_bank_3_number', '1300029358960'),
                'account_holder' => Setting::get('payment_bank_3_holder', 'Deni Firmansyah'),
            ],
            [
                'bank_name' => Setting::get('payment_bank_4_name', 'BCA'),
                'account_number' => Setting::get('payment_bank_4_number', '6395904187'),
                'account_holder' => Setting::get('payment_bank_4_holder', 'Deni Firmansyah'),
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
}
