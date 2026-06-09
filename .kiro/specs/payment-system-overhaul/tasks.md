# Implementation Plan: Payment System Overhaul

## Overview

Replace the invoice-based payment system with a new `payments` table and flow. Remove auto-generate scheduler and auto-approve. Build admin review/approve/reject queue, manual payment recording, updated Excel export, and rename Partner/Mitra to PIC across admin views. All code in PHP/Laravel 11.

## Tasks

- [x] 1. Create `payments` table and Payment model
  - [x] 1.1 Create migration `create_payments_table` with all columns: id, customer_id (FK), periode_bulan (tinyint), periode_tahun (smallint), jumlah (decimal 12,2), metode (enum: transfer, cash), rekening_tujuan (string), bukti_path (nullable), bukti_original_name (nullable), status (enum: pending, approved, rejected), approved_by_user_id (FK users, nullable), approved_at (datetime, nullable), reject_reason (nullable string), catatan (nullable text), created_by_user_id (FK users, nullable), timestamps
    - Add composite index on [customer_id, periode_tahun, periode_bulan]
    - Add index on [status, created_at]
    - _Requirements: 5.1_
  - [x] 1.2 Create `app/Models/Payment.php` with fillable, casts, relationships (customer, approvedBy, createdBy), scopes (pending, approved), bukti_url accessor, approve() method, reject() method
    - _Requirements: 5.2_
  - [x] 1.3 Update `app/Models/Customer.php` — add `payments()` hasMany and `latestPayment()` hasOne(latestOfMany) relationships, remove `invoices()` and `latestInvoice()` relationships
    - _Requirements: 5.2, 1.3_
  - [ ]* 1.4 Write property tests for Payment model state transitions
    - **Property 2: Approve transitions payment to approved state with user and timestamp**
    - **Property 3: Reject transitions payment to rejected state with reason**
    - **Property 4: Only pending payments can be transitioned**
    - **Validates: Requirements 3.3, 3.4, 3.5**

- [x] 2. Disable invoice auto-generation and remove old invoice system
  - [x] 2.1 Comment out `invoices:generate` scheduler entry in `app/Console/Kernel.php`
    - _Requirements: 1.1_
  - [x] 2.2 Create migration `drop_invoices_table` to drop the invoices table
    - _Requirements: 5.3, 1.3_
  - [x] 2.3 Delete `app/Models/Invoice.php`, `app/Http/Controllers/Admin/InvoiceController.php`, and `app/Console/Commands/GenerateMonthlyInvoices.php`
    - _Requirements: 1.3_
  - [x] 2.4 Remove all invoice-related routes from `routes/web.php` (the `invoices` prefix group, billing calendar routes, and any invoice-related API routes like `api/dashboard-live` unpaid/overdue stats)
    - _Requirements: 1.3_

- [x] 3. Checkpoint - Verify migrations and model
  - Run migrations, ensure payments table is created and invoices table is dropped. Ensure all tests pass, ask the user if questions arise.

- [x] 4. Refactor public payment page (PaymentPageController)
  - [x] 4.1 Refactor `app/Http/Controllers/PaymentPageController.php` — remove all invoice logic, remove auto-approve. The `show()` method loads customer by code, displays info + payment accounts. The `submit()` method validates upload, creates Payment record with status=pending, periode=current month/year, jumlah=customer package_price, redirects with "Pembayaran sedang diproses"
    - _Requirements: 1.2, 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 8.1_
  - [x] 4.2 Refactor `resources/views/payments/public.blade.php` — remove invoice selection dropdown, show customer info/package/amount, keep payment account display, show upload form with rekening_tujuan selector (BRI/BNI/Mandiri/BCA/QRIS), show success message "Pembayaran sedang diproses" after upload
    - _Requirements: 2.1, 2.2, 2.3, 2.6_
  - [ ]* 4.3 Write property test for payment upload creating pending record
    - **Property 1: Payment upload always creates pending record with current period**
    - **Validates: Requirements 1.2, 2.2, 8.1**

- [x] 5. Build admin payment review page
  - [x] 5.1 Create `app/Http/Controllers/Admin/PaymentController.php` with methods: reviewIndex (list pending payments), approve (validate pending status, update to approved with user/timestamp, allow period override), reject (validate pending status, record reason)
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 8.2_
  - [x] 5.2 Create `resources/views/admin/payments/review.blade.php` — display pending payments queue with: customer name, area, PIC, period, amount, payment method, rekening, proof image preview (clickable for full view), approve button (with optional period override fields), reject button with reason input
    - _Requirements: 3.1, 3.2, 8.2_
  - [x] 5.3 Register admin payment routes in `routes/web.php` under the admin middleware group: GET /payments/review, POST /payments/{payment}/approve, POST /payments/{payment}/reject
    - _Requirements: 3.1, 3.3, 3.4_

- [x] 6. Build manual payment feature
  - [x] 6.1 Add `manualPaymentForm()` and `manualPaymentStore()` methods to PaymentController — form shows period picker (month/year), amount (default customer package_price), method (transfer/cash), rekening_tujuan (BRI/BNI/Mandiri/BCA/QRIS/Cash), notes. Store creates Payment with status=approved, approved_by=auth user, approved_at=now, created_by=auth user
    - _Requirements: 4.1, 4.2, 4.3, 4.4_
  - [x] 6.2 Create `resources/views/admin/payments/manual.blade.php` — form with customer info header, period month/year dropdowns, amount input (pre-filled), method radio (transfer/cash), rekening dropdown, notes textarea
    - _Requirements: 4.1, 8.3_
  - [x] 6.3 Register manual payment routes: GET /payments/manual/{customer}, POST /payments/manual/{customer}
    - _Requirements: 4.1_
  - [ ]* 6.4 Write property test for manual payment creation
    - **Property 5: Manual payment creates immediately approved record**
    - **Validates: Requirements 4.2, 4.4**

- [~] 7. Checkpoint - Verify payment flow end-to-end
  - Ensure all tests pass. Verify: public upload creates pending payment, admin can approve/reject, admin can create manual payment. Ask the user if questions arise.

- [ ] 8. Update Excel export
  - [~] 8.1 Install maatwebsite/excel: run `composer require maatwebsite/excel`
    - _Requirements: 7.5_
  - [~] 8.2 Refactor `app/Exports/CustomersExport.php` — update query to load `latestPayment` (latest approved payment with approvedBy), update columns to: No, PIC (partner->name), Area, Nama, No. HP (phone), Layanan (package->name), Bayar Rp (package_price), Status, Tgl Berlangganan (billing_start_date), Tgl Bayar (latestPayment->approved_at), Pembayaran (latestPayment->metode), Rekening (latestPayment->rekening_tujuan), Approved by (latestPayment->approvedBy->name), Keterangan (latestPayment->catatan)
    - _Requirements: 7.1, 7.3, 7.4_
  - [~] 8.3 Refactor `resources/views/exports/customers.blade.php` — update table header and row columns to match new export format
    - _Requirements: 7.1_
  - [~] 8.4 Update `CustomerController@exportExcel` to support area filter parameter
    - _Requirements: 7.2_
  - [ ]* 8.5 Write property test for export area filtering
    - **Property 6: Export area filter returns only matching customers**
    - **Validates: Requirements 7.2**

- [ ] 9. Rename Partner/Mitra to PIC across admin views
  - [~] 9.1 Update `resources/views/layouts/sidebar.blade.php` — replace invoice menu items with new payment routes (Review Pembayaran with pending count badge), remove "Tagihan" link
    - _Requirements: 6.2_
  - [~] 9.2 Update all blade files containing "Partner" or "Mitra" labels to use "PIC": `exports/customers.blade.php`, `admin/reports/revenue.blade.php`, `admin/customers/` views, `admin/users/create.blade.php`, `admin/users/edit.blade.php`, `admin/users/index.blade.php`, `admin/inventory/` forms
    - _Requirements: 6.1, 6.2_
  - [~] 9.3 Update `routes/web.php` — remove invoice-related API endpoint (`api/dashboard-live` unpaid_invoices/overdue_invoices), update sidebar badge to use Payment model pending count
    - _Requirements: 6.2_

- [ ] 10. Clean up remaining invoice references
  - [~] 10.1 Remove `app/Services/BillingCalculator.php` if it only serves invoice generation (or refactor if used elsewhere)
    - _Requirements: 1.3_
  - [~] 10.2 Remove or update `app/Console/Commands/SuspendOverdueCustomers.php` since it references invoices (already disabled in scheduler)
    - _Requirements: 1.3_
  - [~] 10.3 Search and remove any remaining references to `Invoice` model, `invoices` table, or `invoice_number` across controllers, views, and other files
    - _Requirements: 1.3_
  - [~] 10.4 Update DashboardController to remove invoice stats and add payment stats (pending count, approved this month)
    - _Requirements: 1.3_

- [~] 11. Final checkpoint - Full verification
  - Ensure all tests pass. Verify no broken references to invoices remain. Verify the complete flow: customer uploads on /bayar → pending payment created → admin reviews → approve/reject works → manual payment works → export works. Ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- The `invoices` table is dropped entirely — ensure no data migration is needed (user confirmed deletion)
- PIC uploads payment proof using the same public /bayar page with the customer's code
- maatwebsite/excel should already be available (CustomersExport exists) but verify installation
- Payment period defaults to current month; admin can override during review
- The `partner` role in the users table remains unchanged (only labels change to "PIC")
