# Design Document: Payment System Overhaul

## Overview

Replace the existing invoice-based payment system with a simpler payment recording flow. The `invoices` table is dropped entirely. A new `payments` table tracks all payment transactions. The public payment page allows proof upload (defaulting to current month), admin reviews and approves/rejects, or admin records manual payments. All "Partner/Mitra" labels are renamed to "PIC". Customer payment data is exportable to Excel.

## Architecture

### Component Diagram

```
┌──────────────────────────────────────────────────────────────┐
│                        Public Layer                           │
│  /bayar?customer_code=X  →  PaymentPageController            │
│  (Upload proof, show status)                                 │
└──────────────────────────┬───────────────────────────────────┘
                           │
┌──────────────────────────▼───────────────────────────────────┐
│                        Admin Layer                            │
│  PaymentController (new)                                     │
│  ├─ reviewIndex()      → Review queue (pending payments)     │
│  ├─ approve()          → Mark approved + record user/time    │
│  ├─ reject()           → Mark rejected + record reason       │
│  ├─ manualPayment()    → Create approved payment (no proof)  │
│  └─ updatePeriod()     → Change period before approving      │
│                                                              │
│  CustomerController (updated)                                │
│  └─ exportExcel()      → Updated export with payment data    │
└──────────────────────────┬───────────────────────────────────┘
                           │
┌──────────────────────────▼───────────────────────────────────┐
│                        Data Layer                             │
│  Payment model (new) ─── payments table (new)                │
│  ├─ customer()         → BelongsTo Customer                  │
│  ├─ approvedBy()       → BelongsTo User                      │
│  ├─ createdBy()        → BelongsTo User                      │
│  └─ scopePending()     → Query scope                         │
│                                                              │
│  Customer model (updated)                                    │
│  └─ payments()         → HasMany Payment                     │
│  └─ latestPayment()    → HasOne (latestOfMany)               │
└──────────────────────────────────────────────────────────────┘
```

## Data Model

### `payments` Table Migration

```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
    $table->tinyInteger('periode_bulan');       // 1-12
    $table->smallInteger('periode_tahun');      // e.g. 2025
    $table->decimal('jumlah', 12, 2);
    $table->enum('metode', ['transfer', 'cash']);
    $table->string('rekening_tujuan');          // BRI, BNI, Mandiri, BCA, QRIS, Cash
    $table->string('bukti_path')->nullable();
    $table->string('bukti_original_name')->nullable();
    $table->enum('status', ['pending', 'approved', 'rejected']);
    $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->dateTime('approved_at')->nullable();
    $table->string('reject_reason')->nullable();
    $table->text('catatan')->nullable();
    $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();

    $table->index(['customer_id', 'periode_tahun', 'periode_bulan']);
    $table->index(['status', 'created_at']);
});
```

### Drop `invoices` Table Migration

```php
Schema::dropIfExists('invoices');
```

### Payment Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    protected $fillable = [
        'customer_id',
        'periode_bulan',
        'periode_tahun',
        'jumlah',
        'metode',
        'rekening_tujuan',
        'bukti_path',
        'bukti_original_name',
        'status',
        'approved_by_user_id',
        'approved_at',
        'reject_reason',
        'catatan',
        'created_by_user_id',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'approved_at' => 'datetime',
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
    ];

    protected $appends = ['bukti_url'];

    // ─── Relationships ───────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // ─── Accessors ───────────────────────────────────────────

    public function getBuktiUrlAttribute(): ?string
    {
        return $this->bukti_path
            ? Storage::disk('public')->url($this->bukti_path)
            : null;
    }

    // ─── Actions ─────────────────────────────────────────────

    public function approve(int $userId, ?int $periodeBulan = null, ?int $periodeTahun = null): void
    {
        $data = [
            'status' => 'approved',
            'approved_by_user_id' => $userId,
            'approved_at' => now(),
        ];

        if ($periodeBulan !== null) {
            $data['periode_bulan'] = $periodeBulan;
        }
        if ($periodeTahun !== null) {
            $data['periode_tahun'] = $periodeTahun;
        }

        $this->update($data);
    }

    public function reject(string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'reject_reason' => $reason,
        ]);
    }
}
```

## Component Design

### 1. PaymentPageController (Refactored)

Handles the public `/bayar` page. Removes all invoice logic and auto-approve behavior.

```php
// show(): Look up customer by code, display info + payment accounts
// submit(): Validate upload, create Payment record as pending, redirect with success message

public function submit(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'customer_code' => 'required|string|max:32',
        'rekening_tujuan' => 'required|string|max:50',
        'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        'catatan' => 'nullable|string|max:1000',
    ]);

    $customer = Customer::whereRaw('UPPER(TRIM(customer_code)) = ?', [
        strtoupper(trim($validated['customer_code']))
    ])->firstOrFail();

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
        'created_by_user_id' => null, // Customer upload, no auth user
    ]);

    return redirect()->back()->with('success', 'Pembayaran sedang diproses');
}
```

### 2. Admin PaymentController (New)

Handles admin payment management: review queue, approve, reject, manual payment.

```php
namespace App\Http\Controllers\Admin;

class PaymentController extends Controller
{
    // reviewIndex(): Show pending payments ordered by created_at asc
    public function reviewIndex()
    {
        $payments = Payment::with(['customer.partner', 'customer.area', 'customer.package'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.payments.review', compact('payments'));
    }

    // approve(): Validate payment is pending, update status
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

    // reject(): Validate payment is pending, record reason
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

    // manualPayment(): Create pre-approved payment from admin
    public function manualPaymentForm(Customer $customer)
    {
        return view('admin.payments.manual', compact('customer'));
    }

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

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Pembayaran manual berhasil dicatat.');
    }
}
```

### 3. CustomersExport (Updated)

Updated to use new `payments` table with specified columns.

```php
// Columns: No | PIC | Area | Nama | No. HP | Layanan | Bayar (Rp) | Status | Tgl Berlangganan | Tgl Bayar | Pembayaran | Rekening | Approved by | Keterangan

// Query loads: customer with partner (PIC), area, package, latestApprovedPayment
// billing_start_date → "Tgl Berlangganan"
// latestApprovedPayment->approved_at → "Tgl Bayar"
// latestApprovedPayment->metode → "Pembayaran"
// latestApprovedPayment->rekening_tujuan → "Rekening"
// latestApprovedPayment->approvedBy->name → "Approved by"
// latestApprovedPayment->catatan → "Keterangan"
```

### 4. Scheduler Modification

Comment out the `invoices:generate` scheduler entry in `Kernel.php`.

### 5. Sidebar and Label Updates

Replace "Partner/Mitra" → "PIC" across admin views. Update sidebar to replace invoice routes with payment routes.

## Routes

```php
// Public payment page (existing, refactored)
Route::get('/bayar', [PaymentPageController::class, 'show'])->name('payment.public.root');
Route::get('/bayar/{customerCode}', [PaymentPageController::class, 'show'])->name('payment.public');
Route::post('/bayar', [PaymentPageController::class, 'submit'])->name('payment.public.submit');

// Admin payment routes (new)
Route::prefix('payments')->name('payments.')->group(function () {
    Route::get('/review', [PaymentController::class, 'reviewIndex'])->name('review');
    Route::post('/{payment}/approve', [PaymentController::class, 'approve'])->name('approve');
    Route::post('/{payment}/reject', [PaymentController::class, 'reject'])->name('reject');
    Route::get('/manual/{customer}', [PaymentController::class, 'manualPaymentForm'])->name('manual');
    Route::post('/manual/{customer}', [PaymentController::class, 'manualPaymentStore'])->name('manual.store');
});
```

## Error Handling

| Scenario | Response |
|----------|----------|
| Invalid customer_code on /bayar | Error flash: "ID pelanggan tidak ditemukan" |
| File too large (>5MB) | Validation error on payment_proof field |
| Invalid file type | Validation error on payment_proof field |
| Approve/reject non-pending payment | Error flash + redirect back |
| Invalid period (month/year) | Validation error |
| Invalid metode value | Validation error |

## File Changes Summary

| File | Action |
|------|--------|
| `database/migrations/xxxx_create_payments_table.php` | Create |
| `database/migrations/xxxx_drop_invoices_table.php` | Create |
| `app/Models/Payment.php` | Create |
| `app/Models/Customer.php` | Update (add payments relationship) |
| `app/Http/Controllers/Admin/PaymentController.php` | Create |
| `app/Http/Controllers/PaymentPageController.php` | Refactor |
| `app/Console/Kernel.php` | Update (disable invoices:generate) |
| `app/Exports/CustomersExport.php` | Refactor |
| `resources/views/admin/payments/review.blade.php` | Create |
| `resources/views/admin/payments/manual.blade.php` | Create |
| `resources/views/payments/public.blade.php` | Refactor |
| `resources/views/exports/customers.blade.php` | Refactor |
| `resources/views/layouts/sidebar.blade.php` | Update |
| `routes/web.php` | Update |
| Multiple blade files | Rename Partner/Mitra → PIC |
| `app/Models/Invoice.php` | Delete |
| `app/Http/Controllers/Admin/InvoiceController.php` | Delete |
| `app/Console/Commands/GenerateMonthlyInvoices.php` | Delete |

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Payment upload always creates pending record with current period

*For any* valid payment proof upload (valid customer code, valid image file), the resulting Payment record SHALL have status `pending`, periode_bulan equal to the current month, and periode_tahun equal to the current year.

**Validates: Requirements 1.2, 2.2, 8.1**

### Property 2: Approve transitions payment to approved state with user and timestamp

*For any* Payment with status `pending`, calling approve with a valid user ID SHALL result in status `approved`, approved_by_user_id set to that user, and approved_at set to a non-null timestamp.

**Validates: Requirements 3.3**

### Property 3: Reject transitions payment to rejected state with reason

*For any* Payment with status `pending` and any non-empty rejection reason string, calling reject SHALL result in status `rejected` and reject_reason set to that string.

**Validates: Requirements 3.4**

### Property 4: Only pending payments can be transitioned

*For any* Payment with status `approved` or `rejected`, attempting to approve or reject SHALL fail without changing the payment record.

**Validates: Requirements 3.5**

### Property 5: Manual payment creates immediately approved record

*For any* valid manual payment input (valid customer, valid period, valid method, valid account), the resulting Payment record SHALL have status `approved`, approved_by_user_id equal to the creating admin, approved_at non-null, and created_by_user_id equal to the creating admin.

**Validates: Requirements 4.2, 4.4**

### Property 6: Export area filter returns only matching customers

*For any* area filter value applied to the export, all customers in the resulting export SHALL belong to that area.

**Validates: Requirements 7.2**

### Property 7: Export field mapping correctness

*For any* customer in the export, the "Tgl Berlangganan" column SHALL equal the customer's billing_start_date, and the "Tgl Bayar" column SHALL equal the approved_at date of the customer's latest approved payment.

**Validates: Requirements 7.3, 7.4**
