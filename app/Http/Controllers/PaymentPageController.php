<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentPageController extends Controller
{
    public function show(Request $request, ?string $customerCode = null)
    {
        $customerCode = strtoupper(trim((string) ($customerCode ?: $request->query('customer_code', ''))));

        $customer = null;

        if ($customerCode !== '') {
            $customer = Customer::query()
                ->with(['package', 'area'])
                ->whereRaw('UPPER(TRIM(customer_code)) = ?', [$customerCode])
                ->first();

            if (!$customer) {
                return view('payments.public', [
                    'customerCode' => $customerCode,
                    'customer' => null,
                    'paymentSettings' => $this->paymentSettings(),
                ])->with('error', 'ID pelanggan tidak ditemukan.');
            }
        }

        return view('payments.public', [
            'customerCode' => $customerCode,
            'customer' => $customer,
            'paymentSettings' => $this->paymentSettings(),
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_code' => 'required|string|max:32',
            'rekening_tujuan' => 'required|string|max:50',
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $customerCode = strtoupper(trim((string) $validated['customer_code']));
        $customer = Customer::query()
            ->whereRaw('UPPER(TRIM(customer_code)) = ?', [$customerCode])
            ->first();

        if (!$customer) {
            return back()->withInput()->with('error', 'ID pelanggan tidak ditemukan.');
        }

        $file = $request->file('payment_proof');
        $storedPath = $file->store("payment-proofs/customer-{$customer->id}", 'public');

        Payment::create([
            'customer_id' => $customer->id,
            'periode_bulan' => now()->month,
            'periode_tahun' => now()->year,
            'jumlah' => $customer->package_price,
            'metode' => 'transfer',
            'rekening_tujuan' => $validated['rekening_tujuan'],
            'bukti_path' => $storedPath,
            'bukti_original_name' => $file->getClientOriginalName(),
            'status' => 'pending',
            'catatan' => $validated['catatan'] ?? null,
            'created_by_user_id' => null,
        ]);

        // Notify admin/finance via website notification
        AdminNotification::notify(
            'payment_pending',
            'Pembayaran Baru',
            "{$customer->name} ({$customer->customer_code}) - Rp " . number_format($customer->package_price, 0, ',', '.') . " via {$validated['rekening_tujuan']}",
            'bx-money',
            'green',
            '/admin/payments/review'
        );

        return redirect()->back()->with('success', 'Pembayaran sedang diproses');
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
                    'Scan QRIS resmi NETKING, bayar sesuai nominal tagihan, lalu unggah bukti pembayaran agar admin dapat memverifikasi pembayaran Anda.'
                ),
            ]
            : null;

        return [
            'accounts' => $accounts,
            'qris' => $qris,
            'notes' => Setting::get(
                'manual_payment_notes',
                'Transfer atau bayar via QRIS sesuai nominal tagihan, lalu upload bukti pembayaran agar admin bisa memverifikasi pembayaran Anda.'
            ),
        ];
    }
}
