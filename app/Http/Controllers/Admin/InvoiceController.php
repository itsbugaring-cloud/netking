<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\CommissionLog;
use App\Models\ActivityLog;
use App\Services\WhatsAppService;
use App\Services\BillingCalculator;
use Illuminate\Http\Request;
use App\Models\Setting;

class InvoiceController extends Controller
{
    private function confirmPartnerCommission(Invoice $invoice): void
    {
        if (! $invoice->customer->partner_id) {
            return;
        }

        $existing = CommissionLog::where('invoice_id', $invoice->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            $existing->update(['status' => 'unpaid']);
            return;
        }

        $commissionAmount = round(((float) $invoice->amount) / 3);

        if ($commissionAmount > 0) {
            CommissionLog::create([
                'user_id'     => $invoice->customer->partner_id,
                'customer_id' => $invoice->customer_id,
                'invoice_id'  => $invoice->id,
                'amount'      => $commissionAmount,
                'month'       => now()->month,
                'year'        => now()->year,
                'status'      => 'unpaid',
            ]);
        }
    }

    private function resolveRejectReason(Request $request): ?string
    {
        $customReason = trim((string) $request->input('reject_reason_custom', ''));
        if ($customReason !== '') {
            return $customReason;
        }

        $presetReason = trim((string) $request->input('reject_reason_preset', ''));
        return $presetReason !== '' ? $presetReason : null;
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
     * Display invoice list
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $query = Invoice::with(['customer.partner', 'customer.area'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($qc) use ($search) {
                        $qc->where('name', 'like', '%' . $search . '%')
                            ->orWhere('pppoe_user', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('customer.partner', function ($qp) use ($search) {
                        $qp->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by month/year
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereYear('due_date', $request->year)
                ->whereMonth('due_date', $request->month);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('due_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('due_date', '<=', $request->to_date);
        }

        $invoices = $query->paginate($perPage)->withQueryString();

        // Get stats
        $stats = [
            'total' => Invoice::count(),
            'unpaid' => Invoice::where('status', 'unpaid')->count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'overdue' => Invoice::overdue()->count(),
            'total_unpaid_amount' => Invoice::where('status', 'unpaid')->sum('amount'),
            'total_paid_amount' => Invoice::where('status', 'paid')->sum('amount'),
        ];

        return view('admin.invoices.index', compact('invoices', 'stats', 'perPage'));
    }

    /**
     * Show invoice details
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer.partner', 'customer.area']);
        $paymentSettings = $this->paymentSettings();

        return view('admin.invoices.show', compact('invoice', 'paymentSettings'));
    }

    /**
     * Generate PDF for invoice
     */
    public function pdf(Invoice $invoice)
    {
        $invoice->load(['customer.partner', 'customer.area']);
        // Fallback aman produksi: render halaman invoice printable
        // tanpa dependency PDF eksternal yang tidak tersedia di server.
        return response()
            ->view('admin.invoices.pdf', compact('invoice'))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Mark invoice as paid manually
     */
    public function markAsPaid(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice already paid');
        }

        $invoice->markAsPaid($request->payment_method);

        $this->confirmPartnerCommission($invoice);

        // Log activity
        \App\Models\ActivityLog::logActivity(
            'payment',
            "Manual payment confirmed for invoice {$invoice->invoice_number}",
            auth()->id(),
            Invoice::class,
            $invoice->id,
            ['method' => $request->payment_method]
        );

        return back()->with('success', 'Invoice marked as paid successfully');
    }

    /**
     * Payment proof review queue — show all submitted proofs
     */
    public function paymentQueue(Request $request)
    {
        $invoices = Invoice::with(['customer.partner', 'customer.area'])
            ->where('payment_review_status', 'submitted')
            ->where('status', 'unpaid')
            ->orderBy('payment_proof_submitted_at', 'asc')
            ->get();

        $pendingCount = Invoice::where('payment_review_status', 'submitted')
            ->where('status', 'unpaid')
            ->count();

        $paymentSettings = $this->paymentSettings();

        return view('admin.invoices.payment-queue', compact('invoices', 'pendingCount', 'paymentSettings'));
    }

    /**
     * Approve payment proof — mark as paid + send WA confirmation
     */
    public function approvePaymentProof(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice sudah lunas.');
        }

        if ($invoice->payment_review_status !== 'submitted' || ! $invoice->payment_proof_path) {
            return back()->with('error', 'Bukti bayar ini sudah tidak dalam status menunggu review.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($invoice, $request) {
            $invoice->markAsPaid($request->payment_method);
            $this->confirmPartnerCommission($invoice);

            ActivityLog::logActivity('payment',
                "Bukti bayar disetujui untuk {$invoice->invoice_number}",
                auth()->id(), Invoice::class, $invoice->id,
                ['method' => $request->payment_method]
            );
        });

        try {
            $wa = new WhatsAppService();
            $wa->sendPaymentConfirmation(
                $invoice->customer->phone ?? '',
                $invoice->customer->name,
                $invoice->invoice_number,
                $invoice->amount,
                $request->payment_method
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('WA payment confirm failed: ' . $e->getMessage());
        }

        return back()->with('success', "Invoice {$invoice->invoice_number} dikonfirmasi lunas.");
    }

    /**
     * Reject payment proof — reset review status + send WA rejection
     */
    public function rejectPaymentProof(Request $request, Invoice $invoice)
    {
        $request->validate([
            'reject_reason_preset' => 'nullable|string|max:500',
            'reject_reason_custom' => 'nullable|string|max:500',
        ]);

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice sudah lunas, tidak bisa ditolak.');
        }

        if ($invoice->payment_review_status !== 'submitted' || ! $invoice->payment_proof_path) {
            return back()->with('error', 'Bukti bayar ini sudah tidak dalam status menunggu review.');
        }

        $rejectReason = $this->resolveRejectReason($request);
        if (! $rejectReason) {
            return back()->with('error', 'Alasan penolakan wajib diisi.');
        }

        $invoice->update([
            'payment_review_status' => 'rejected',
            'payment_reviewed_at'   => now(),
            'payment_reject_reason' => $rejectReason,
        ]);

        // WhatsApp notification
        try {
            $wa = new WhatsAppService();
            $wa->sendPaymentRejected(
                $invoice->customer->phone ?? '',
                $invoice->customer->name,
                $invoice->invoice_number,
                $rejectReason
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('WA payment reject failed: ' . $e->getMessage());
        }

        ActivityLog::logActivity('payment',
            "Bukti bayar ditolak untuk {$invoice->invoice_number}: {$rejectReason}",
            auth()->id(), Invoice::class, $invoice->id,
            ['reason' => $rejectReason]
        );

        return back()->with('success', "Bukti bayar {$invoice->invoice_number} ditolak, notifikasi WA dikirim.");
    }

    /**
     * Cancel invoice
     */
    public function cancel(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cannot cancel paid invoice');
        }

        $invoice->update(['status' => 'cancelled']);

        return back()->with('success', 'Invoice cancelled successfully');
    }

    /**
     * Bulk generate invoices for all active customers for a given month/year
     */
    public function bulkGenerate(Request $request)
    {
        /** @var BillingCalculator $billing */
        $billing = app(BillingCalculator::class);

        $validated = $request->validate([
            'month'    => 'required|integer|min:1|max:12',
            'year'     => 'required|integer|min:2020|max:2030',
            'due_days' => 'nullable|integer|min:1|max:31',
        ]);

        $month   = (int) $validated['month'];
        $year    = (int) $validated['year'];
        $dueDay  = (int) ($validated['due_days'] ?? config('billing.invoice_due_day', 20));
        $dueDate = $billing->resolveDueDateForPeriod($year, $month, $dueDay);

        // Active customers without existing invoice for this month/year
        $customers = Customer::where('status', 'active')
            ->whereDoesntHave('invoices', function ($q) use ($month, $year) {
                $q->where(function ($qq) use ($month, $year) {
                    $qq->where(function ($qPeriod) use ($month, $year) {
                        $qPeriod->whereNotNull('period_year')
                            ->where('period_month', $month)
                            ->where('period_year', $year);
                    })->orWhere(function ($qLegacy) use ($month, $year) {
                        $qLegacy->whereNull('period_year')
                            ->whereMonth('due_date', $month)
                            ->whereYear('due_date', $year);
                    });
                });
            })
            ->with('package')
            ->get();

        $created = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($customers, $month, $year, $dueDate, $billing, &$created) {
            foreach ($customers as $customer) {
                $calculated = $billing->calculateForPeriod($customer, $year, $month);
                if ($calculated['skip']) {
                    continue;
                }

                $invoice = Invoice::create([
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'customer_id'    => $customer->id,
                    'amount'         => $calculated['amount'],
                    'base_amount'    => $calculated['base_amount'],
                    'billed_days'    => $calculated['billed_days'],
                    'period_days'    => $calculated['period_days'],
                    'period_month'   => $calculated['period_month'],
                    'period_year'    => $calculated['period_year'],
                    'is_prorated'    => $calculated['is_prorated'],
                    'status'         => 'unpaid',
                    'due_date'       => $dueDate,
                ]);

                // Create pending commission if customer has partner
                if ($customer->partner_id) {
                    $commAmt = round(((float) $invoice->amount) / 3);
                    if ($commAmt > 0) {
                        CommissionLog::create([
                            'user_id'     => $customer->partner_id,
                            'customer_id' => $customer->id,
                            'invoice_id'  => $invoice->id,
                            'amount'      => $commAmt,
                            'month'       => $month,
                            'year'        => $year,
                            'status'      => 'pending',
                        ]);
                    }
                }

                $created++;
            }
        });

        $monthNames = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        ActivityLog::logActivity(
            'bulk_generate_invoices',
            "Bulk generated {$created} invoices for {$monthNames[$month]} {$year}",
            auth()->id()
        );

        return back()->with('success', "Berhasil membuat {$created} tagihan baru untuk periode {$monthNames[$month]} {$year}.");
    }
}
