# Implementation Plan: Payment Page Redesign

## Overview

Rewrite the single Blade template `resources/views/payments/public.blade.php` to replace all custom CSS and Boxicons with Tabler framework components loaded from CDN. No backend, controller, or route changes are required. The implementation is broken into incremental sections of the page, followed by integration tests verifying the migration.

## Tasks

- [x] 1. Rewrite page shell and header with Tabler framework
  - [x] 1.1 Replace HTML head, body structure, and page header with Tabler CDN and layout
    - Remove all custom CSS variables and inline `<style>` block
    - Add Tabler CSS CDN link (`@tabler/core@latest/dist/css/tabler.min.css`)
    - Add Tabler Icons webfont CDN link (`@tabler/icons-webfont@latest/tabler-icons.min.css`)
    - Add Inter font with `font-feature-settings` matching admin panel
    - Set up `<body class="d-flex flex-column">` with `page page-center` and `container-xl`
    - Create page header with avatar icon (`ti ti-credit-card`), title "Pembayaran Netking", and description text
    - Remove Boxicons CDN link
    - _Requirements: 1.1, 1.2, 1.3, 1.8, 2.1, 2.2_

- [x] 2. Implement search card and quick-guide section
  - [x] 2.1 Build the search form card with Tabler components
    - Create a Tabler card with header "Cek Tagihan Pelanggan" and subtitle
    - Convert alerts from `.pay-alert-*` to `.alert .alert-success`, `.alert .alert-danger`
    - Convert search input from `.pay-input` to `.form-control`
    - Convert submit button from `.pay-btn .pay-btn-primary` to `.btn .btn-primary` with `ti ti-search` icon
    - Preserve GET form action, `old()` value binding, and `customer_code` parameter
    - Add 3 quick-guide highlight items below the form using Tabler card/utility classes
    - Replace Boxicons (`bx bx-search-alt`, `bx bx-credit-card`, `bx bx-check-shield`) with Tabler Icons (`ti ti-search`, `ti ti-cash`, `ti ti-shield-check`)
    - _Requirements: 1.4, 1.5, 1.6, 1.8, 3.1, 3.5, 3.6, 8.1_

- [x] 3. Implement payment methods sidebar card
  - [x] 3.1 Build payment methods card with bank list and QRIS
    - Create Tabler card with header "Metode Pembayaran Resmi"
    - Render bank accounts list with bank name, account number, and holder name
    - Render QRIS image as clickable link opening in new tab with `alt` text matching QRIS label
    - Render QRIS notes below the image when configured
    - Render general payment instruction note from `$paymentSettings['notes']`
    - Handle edge cases: no bank accounts, no QRIS configured
    - _Requirements: 1.4, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6_

- [x] 4. Implement responsive grid layout
  - [x] 4.1 Wire search card and payment methods card into responsive two-column grid
    - Use `row` > `col-lg-7` for search card and `col-lg-5` for payment methods
    - Add `col-md-7` / `col-md-5` breakpoints for medium viewports
    - Ensure columns stack to full width below 768px
    - Apply same grid pattern to payment guide and FAQ row
    - _Requirements: 2.3, 2.4, 2.5, 7.1, 7.2, 7.3, 7.4_

- [x] 5. Implement customer data and invoice list card
  - [x] 5.1 Build customer info display and invoice list with Tabler components
    - Create full-width Tabler card for customer data (conditional on `$customer`)
    - Display customer name, code, area, and package in a responsive grid of stat cards
    - Render "no active invoices" success alert when `$invoices->isEmpty()`
    - Render warning alert when multiple invoices exist
    - Display each invoice with: invoice number, due date, prorated badge, amount in Rupiah
    - Add status badges: `.badge .bg-blue-lt` (Belum Lunas), `.badge .bg-orange-lt` (Jatuh Tempo), `.badge .bg-yellow-lt` (Menunggu Review)
    - Add "Pilih Tagihan" button as `.btn .btn-outline-secondary` linking with invoice ID
    - Show "Tagihan Dipilih" as disabled `.btn .btn-primary` for selected invoice
    - Display rejection reason when `payment_review_status === 'rejected'`
    - Auto-select single invoice behavior preserved via controller logic
    - _Requirements: 1.4, 1.7, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8_

- [x] 6. Implement upload form card
  - [x] 6.1 Build payment proof upload form with Tabler form components
    - Create Tabler card "Upload Bukti Pembayaran" (conditional on `$selectedInvoice`)
    - Add payment instructions panel showing selected invoice number and amount
    - Show warning alert when `payment_review_status === 'submitted'`
    - Convert payment method dropdown from `.pay-select` to `.form-select`
    - Convert file input from `.pay-file` to `.form-control` with accept attribute
    - Convert textarea from `.pay-textarea` to `.form-control`
    - Convert labels from `.pay-label` to `.form-label`
    - Preserve form POST action, CSRF token, hidden inputs, `multipart/form-data` encoding
    - Preserve `old()` bindings and pre-fill from `$selectedInvoice` properties
    - Show link to existing proof when `payment_proof_url` exists
    - Dynamic submit button text: "Ganti Bukti Pembayaran" vs "Kirim Bukti Pembayaran"
    - Replace upload icon `bx bx-upload` with `ti ti-upload`
    - Use responsive two-column grid (`col-lg-7` instructions / `col-lg-5` form) when invoice selected
    - _Requirements: 1.4, 1.5, 1.8, 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7_

- [x] 7. Implement payment guide and FAQ cards
  - [x] 7.1 Build "Tata Cara Bayar" and "Pertanyaan Umum" cards
    - Create Tabler card for payment guide with 4 numbered steps
    - Each step includes numeric indicator, title, and description
    - Preserve exact step content: enter ID, select invoice, pay exact amount, upload proof
    - Create Tabler card for FAQ with 3 items showing question and answer
    - Preserve exact FAQ content: ID not found, not yet confirmed, multiple invoices
    - Display both sections in responsive two-column grid (`col-lg-7` / `col-lg-5`)
    - Both sections visible without authentication
    - _Requirements: 1.4, 8.1, 8.2, 8.3, 8.4_

- [x] 8. Checkpoint - Verify template renders correctly
  - Ensure the rewritten template loads without errors by visiting `/bayar` in the browser.
  - Verify no custom CSS variables remain, no Boxicons references, Tabler CDN loads.
  - Ensure all tests pass, ask the user if questions arise.

- [x] 9. Write integration tests
  - [x] 9.1 Create Laravel Feature Test for Tabler framework adoption
    - Create `tests/Feature/PaymentPageRedesignTest.php`
    - Test page loads with status 200 and contains `tabler.min.css` CDN link
    - Test page contains `container-xl` class
    - Test page does NOT contain `boxicons` CDN link or `bx bx-` class references
    - Test page does NOT contain custom CSS variables (`--bg-a`, `--surface`, `--primary-color`)
    - Test page contains `ti ti-` icon classes (Tabler Icons)
    - _Requirements: 1.1, 1.3, 1.8_

  - [ ]* 9.2 Write property test for Tabler framework exclusivity
    - **Property 1: Tabler framework exclusivity**
    - Test that for any render state (no customer, with customer, with invoices, with selected invoice), the HTML contains Tabler CSS CDN, does NOT contain custom CSS variables, and does NOT contain Boxicons classes
    - Use multiple data scenarios: empty page, customer with no invoices, customer with multiple invoices, selected invoice with upload form
    - **Validates: Requirements 1.1, 1.3, 1.8**

  - [ ]* 9.3 Write feature tests for component rendering
    - Test search form contains `.form-control` and `.btn.btn-primary`
    - Test invoice badges render with `.badge` class
    - Test alerts render with `.alert.alert-danger` / `.alert.alert-success`
    - Test upload form contains `.form-select`, `.form-control` for file input
    - Test responsive grid classes present: `col-lg-7`, `col-lg-5`, `col-md-7`, `col-md-5`
    - Test QRIS image has `alt` attribute
    - Test payment guide has 4 steps and FAQ has 3 items
    - _Requirements: 1.4, 1.5, 1.6, 1.7, 2.3, 5.2, 6.1, 7.4, 8.1, 8.2_

- [x] 10. Final checkpoint - Ensure all tests pass
  - Run `php artisan test --filter=PaymentPageRedesign` to verify all tests pass.
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- This is a single-file rewrite — all tasks modify `resources/views/payments/public.blade.php`
- No backend, controller, or route changes are needed
- The controller passes the same data variables unchanged
- Property tests validate the universal constraint that no legacy styles leak through regardless of page state
- Checkpoints ensure incremental validation

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["2.1", "3.1"] },
    { "id": 2, "tasks": ["4.1", "5.1"] },
    { "id": 3, "tasks": ["6.1", "7.1"] },
    { "id": 4, "tasks": ["9.1"] },
    { "id": 5, "tasks": ["9.2", "9.3"] }
  ]
}
```
