# Design Document: Payment Page Redesign

## Overview

This design covers the redesign of the public payment page (`/bayar`) from its current custom CSS implementation to the Tabler UI framework, achieving visual consistency with the admin panel while preserving all existing functionality.

**Scope**: Replace the single Blade template (`resources/views/payments/public.blade.php`) — no controller changes, no route changes, no database changes. The redesign is purely a front-end template rewrite.

**Key Constraints**:
- The page is standalone (no auth, no admin sidebar/header) so Tabler CSS/JS must be loaded independently
- All existing data bindings (`$customer`, `$invoices`, `$selectedInvoice`, `$paymentSettings`, `$customerCode`) remain unchanged
- All form actions, CSRF tokens, route helpers, `old()` values, and session flash behavior are preserved
- Zero custom CSS variables — all styling via Tabler utility classes and components

## Architecture

The page uses a simple single-template architecture with no changes to the MVC layer:

```
┌─────────────────────────────────────────────────────┐
│  Browser Request: GET /bayar?customer_code=NK000123 │
└───────────────────────────┬─────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────┐
│         PaymentPageController::show()               │
│  (NO CHANGES - same logic, same data passed)        │
└───────────────────────────┬─────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────┐
│    payments/public.blade.php (REDESIGNED)            │
│    ┌─────────────────────────────────────────────┐  │
│    │  Tabler CSS/JS from CDN                     │  │
│    │  Inter font + font-feature-settings         │  │
│    │  Tabler Icons webfont                       │  │
│    ├─────────────────────────────────────────────┤  │
│    │  Page Layout (container-xl, no sidebar)     │  │
│    │  ├── Page Header (branding + description)   │  │
│    │  ├── Row: col-lg-7 + col-lg-5              │  │
│    │  │   ├── Search Card + Quick Guide         │  │
│    │  │   └── Payment Methods Card              │  │
│    │  ├── Customer Data + Invoice List Card      │  │
│    │  ├── Upload Form Card                       │  │
│    │  ├── Payment Guide Card                     │  │
│    │  └── FAQ Card                               │  │
│    └─────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
```

### Design Decisions

1. **Standalone page without admin layout inheritance**: The page does not extend `admin.layout.app` because it has no sidebar, no auth-based header, and no footer. Instead, it loads Tabler CSS/JS directly in its own `<head>` and `<body>`, mirroring the CDN sources from the admin layout.

2. **Same CDN sources as admin panel**: Uses `https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css` and `https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css` — identical to `admin/layout/app.blade.php`.

3. **No custom CSS variables**: The current page defines 14 custom CSS variables. All are replaced by Tabler's built-in design tokens and utility classes.

4. **Minimal inline styles**: Only the `:root` font-family override and `body` font-feature-settings from the admin layout are carried over. No other custom styles.

## Components and Interfaces

### HTML Structure (Tabler Components)

#### Page Shell

```html
<body class="d-flex flex-column">
  <div class="page page-center">
    <div class="container-xl">
      <!-- All content -->
    </div>
  </div>
</body>
```

Uses `page-center` since there's no sidebar, and `container-xl` matching the admin panel container.

#### Page Header

```html
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row align-items-center">
      <div class="col-auto">
        <span class="avatar avatar-lg bg-primary-lt">
          <i class="ti ti-credit-card"></i>
        </span>
      </div>
      <div class="col">
        <h2 class="page-title">Pembayaran Netking</h2>
        <div class="text-secondary">Description text</div>
      </div>
    </div>
  </div>
</div>
```

#### Content Cards

All sections use Tabler card structure:

```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Section Title</h3>
  </div>
  <div class="card-body">
    <!-- Content -->
  </div>
</div>
```

#### Form Elements

| Current Class | Tabler Replacement |
|---|---|
| `.pay-input` | `.form-control` |
| `.pay-select` | `.form-select` |
| `.pay-textarea` | `.form-control` (on `<textarea>`) |
| `.pay-file` | `.form-control` (on `<input type="file">`) |
| `.pay-btn-primary` | `.btn .btn-primary` |
| `.pay-btn-secondary` | `.btn .btn-outline-secondary` |
| `.pay-label` | `.form-label` |
| `.pay-help` | `.form-hint` or `.text-secondary` |

#### Alert Components

| Current Class | Tabler Replacement |
|---|---|
| `.pay-alert-success` | `.alert .alert-success` |
| `.pay-alert-error` | `.alert .alert-danger` |
| `.pay-alert-warning` | `.alert .alert-warning` |

#### Badge Components

| Current Logic | Tabler Class |
|---|---|
| `st-unpaid` (Belum Lunas) | `.badge .bg-blue-lt` |
| `st-overdue` (Jatuh Tempo) | `.badge .bg-orange-lt` |
| `st-review` (Menunggu Review) | `.badge .bg-yellow-lt` |

#### Icon Mapping (Boxicons → Tabler Icons)

| Current (Boxicons) | Replacement (Tabler Icons) |
|---|---|
| `bx bx-credit-card-front` | `ti ti-credit-card` |
| `bx bx-search` | `ti ti-search` |
| `bx bx-search-alt` | `ti ti-search` |
| `bx bx-credit-card` | `ti ti-cash` |
| `bx bx-check-shield` | `ti ti-shield-check` |
| `bx bx-upload` | `ti ti-upload` |
| `bx bx-id-card` | `ti ti-id` |
| `bx bx-qr-scan` | `ti ti-qrcode` |
| `bx bx-cloud-upload` | `ti ti-cloud-upload` |

### Layout Grid Structure

```
┌────────────────────────────────────────────────────────┐
│  Page Header (full width)                              │
├────────────────────────────────────────────────────────┤
│  row                                                   │
│  ┌──────────────────────────┬─────────────────────────┐│
│  │ col-lg-7                 │ col-lg-5                ││
│  │ ┌──────────────────────┐ │ ┌─────────────────────┐││
│  │ │ Search + Quick Guide │ │ │ Payment Methods     │││
│  │ └──────────────────────┘ │ └─────────────────────┘││
│  └──────────────────────────┴─────────────────────────┘│
├────────────────────────────────────────────────────────┤
│  Customer Data + Invoice List Card (full width)        │
│  (conditional: only shown when $customer exists)       │
├────────────────────────────────────────────────────────┤
│  row (conditional: only when $selectedInvoice)         │
│  ┌──────────────────────────┬─────────────────────────┐│
│  │ col-lg-7                 │ col-lg-5                ││
│  │ Payment Instructions     │ Upload Form             ││
│  └──────────────────────────┴─────────────────────────┘│
├────────────────────────────────────────────────────────┤
│  row                                                   │
│  ┌──────────────────────────┬─────────────────────────┐│
│  │ col-lg-7                 │ col-lg-5                ││
│  │ Payment Guide (4 steps)  │ FAQ (3 items)           ││
│  └──────────────────────────┴─────────────────────────┘│
└────────────────────────────────────────────────────────┘
```

On viewports below 768px, all columns stack to `col-12`.

### Responsive Breakpoint Strategy

| Viewport | Grid Behavior |
|---|---|
| ≥992px (lg) | Two-column layout: `col-lg-7` + `col-lg-5` |
| 768px–991px (md) | Two-column: `col-md-7` + `col-md-5` |
| <768px | Single column, all elements stack |

Tabler's grid is Bootstrap 5-based, so `col-md-*` triggers at 768px, satisfying Requirement 7's breakpoint.

## Data Models

No changes to data models. The template receives the same variables from the controller:

| Variable | Type | Description |
|---|---|---|
| `$customerCode` | `string` | Submitted customer code (uppercase, trimmed) |
| `$customer` | `?Customer` | Customer model with `package` and `area` relations, or null |
| `$invoices` | `Collection<Invoice>` | Unpaid invoices ordered by due_date, empty if no customer |
| `$selectedInvoice` | `?Invoice` | The currently selected invoice, or null |
| `$paymentSettings` | `array` | Contains `accounts` (array of bank objects), `qris` (object or null), `notes` (string) |

### Invoice Model Properties Used

| Property | Type | Usage |
|---|---|---|
| `invoice_number` | string | Display |
| `due_date` | Carbon | Format and comparison |
| `is_prorated` | boolean | Prorated badge |
| `amount` | numeric | Formatted as Rupiah |
| `payment_review_status` | string | "submitted", "rejected", null |
| `payment_reject_reason` | ?string | Rejection reason display |
| `payment_method` | ?string | Pre-select dropdown |
| `payment_proof_notes` | ?string | Pre-fill textarea |
| `payment_proof_url` | ?string | Link to existing proof |

### Payment Settings Structure

```php
[
    'accounts' => [
        ['bank_name' => 'BRI', 'account_number' => '...', 'account_holder' => '...'],
        // ...
    ],
    'qris' => [
        'label' => 'QRIS NETKING',
        'image_url' => 'https://...',
        'notes' => '...',
    ] | null,
    'notes' => 'General payment instructions...',
]
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

This feature is primarily a UI template redesign. Most acceptance criteria concern visual styling and layout, which are not suitable for property-based testing. However, the framework migration constraint (no custom CSS variables, no legacy icon classes) can be verified as a universal property across all possible render states of the page.

### Property 1: Tabler framework exclusivity

*For any* rendered output of the payment page (regardless of customer data, invoice state, or payment settings), the HTML SHALL contain the Tabler CSS CDN link, SHALL NOT contain any custom CSS variable declarations (e.g., `--bg-a`, `--surface`, `--primary-color`), and SHALL NOT contain any Boxicons class references (`bx bx-`).

**Validates: Requirements 1.1, 1.3, 1.8**

## Error Handling

Error handling is **unchanged** — it remains in the controller and session flash system. The template simply renders errors differently using Tabler alert components:

| Scenario | Current Behavior | New Behavior |
|---|---|---|
| `session('success')` | Custom green alert | `.alert .alert-success` with `ti ti-check` icon |
| `session('error')` | Custom red alert | `.alert .alert-danger` with `ti ti-alert-circle` icon |
| `$errors->any()` | Custom red alert | `.alert .alert-danger` with `ti ti-alert-circle` icon |
| Customer not found | Controller returns view with error flash | Same — renders in Tabler alert |
| Validation failure | Redirect back with errors | Same — `$errors->first()` in Tabler alert |
| Paid/cancelled invoice | Redirect back with error flash | Same |

No new error states are introduced by this redesign.

## Testing Strategy

### Why Property-Based Testing Does NOT Apply

This feature is a **pure UI template redesign**. It replaces CSS classes and HTML structure in a Blade template without modifying any backend logic, data transformations, or algorithms. There are no pure functions with varied input/output behavior to test with PBT. The acceptance criteria are about:
- Correct CSS class usage (Tabler vs custom)
- Layout structure (grid columns, responsiveness)
- Component rendering (cards, badges, alerts)
- Icon replacement (Boxicons → Tabler Icons)

These are best validated through visual inspection, snapshot testing, and example-based integration tests.

### Testing Approach

#### 1. Manual Visual Testing (Primary)

- **Side-by-side comparison**: Open old page (via git stash) and new page at the same viewport widths
- **Responsive checks**: Test at 320px, 768px, 992px, and 1920px viewport widths
- **Touch target verification**: Use browser DevTools to measure button/link sizes on mobile viewport
- **Font size audit**: Verify no text renders below 14px at any viewport

#### 2. Example-Based Integration Tests (Laravel Feature Tests)

Test the rendered HTML to verify Tabler component usage:

| Test Case | Assertion |
|---|---|
| Page loads without customer code | Response 200, contains `tabler.min.css` link, contains `container-xl` class |
| Page loads with valid customer code | Contains `.card`, `.card-header`, `.card-body` elements |
| Page shows alerts correctly | `session('error')` renders as `.alert.alert-danger` |
| Page shows invoice badges | Invoice status renders with `.badge` class |
| Search form uses Tabler classes | Form input has `.form-control`, button has `.btn.btn-primary` |
| No Boxicons present | Response does NOT contain `bx bx-` or `boxicons` CDN link |
| No custom CSS variables | Response does NOT contain `--bg-a`, `--surface`, or any custom variable |
| Upload form structure correct | Contains `.form-select`, `.form-control` for file input |
| QRIS image renders with alt text | `<img>` has `alt` attribute matching QRIS label |
| Responsive grid classes present | Contains `col-lg-7`, `col-lg-5`, `col-md-7`, `col-md-5` |
| Tabler Icons used | Contains `ti ti-` icon classes |

#### 3. Browser-Based Testing (Optional Enhancement)

If Laravel Dusk is available:
- Screenshot comparison at multiple viewport widths
- Click-through of invoice selection flow
- File upload form interaction
- Mobile viewport touch target size validation

### Test File Location

```
tests/Feature/PaymentPageRedesignTest.php
```

### Key Assertions for Each Requirement

| Requirement | Key Test Assertion |
|---|---|
| R1: Tabler Adoption | No boxicons, no custom CSS vars, Tabler CDN loaded |
| R2: Layout Structure | `container-xl`, responsive grid classes, content order |
| R3: Customer Search | `.form-control` input, `.btn.btn-primary` button, GET form |
| R4: Invoice Display | `.badge` for status, Rupiah formatting preserved |
| R5: Payment Methods | Bank cards in Tabler card structure, QRIS image with alt |
| R6: Upload Form | `.form-select`, `.form-control` file, multipart form |
| R7: Responsive | `col-md-*`/`col-lg-*` grid classes present |
| R8: Info Content | 4 steps present, 3 FAQ items present, content preserved |
