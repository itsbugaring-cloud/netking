# Design Document: UI Component Unification

## Overview

This design delivers a unified visual language across both `layouts.app` (dark-themed Bootstrap 5) and `admin.layout.app` (Tabler white-themed) by injecting CSS overrides and a small Select2 auto-init script. No build tools, no new Blade components — just CSS custom properties, CSS rules, and a jQuery snippet.

The architecture follows a layered approach:
1. **Design tokens** (`:root` CSS variables) define shared values
2. **Component CSS overrides** consume those tokens via `var()` references
3. **A Select2 auto-init script** bootstraps dropdowns without manual JS calls per page

---

## Architecture

### Injection Points

| Layout | Style injection location | Script injection location |
|--------|--------------------------|---------------------------|
| `layouts/app.blade.php` | After existing `</style>` closing tag (before `@yield('styles')`) — add a new `<style>` block | Already has Select2 init; extend it |
| `admin/layout/app.blade.php` | Before `@stack('styles')` — add a new `<style>` block after existing `</style>` | Before `@stack('scripts')` — add CDN links + init script |

### Dependency Graph

```
:root tokens (--nk-primary, --nk-height, --nk-height-sm, --nk-radius)
      │
      ├── Dropdown CSS (Select2 overrides)
      ├── Button CSS (.btn overrides)
      ├── Modal CSS (.modal-content overrides)
      ├── Table Controls CSS (search, filter, pagination)
      │
      └── Select2 Auto-Init Script (jQuery)
```

---

## Components and Interfaces

### 1. Design Tokens (CSS Custom Properties)

Identical block injected into both layouts at the `:root` level:

```css
:root {
  --nk-primary: #2563eb;
  --nk-height: 34px;
  --nk-height-sm: 30px;
  --nk-radius: 6px;
}
```

All component CSS references these tokens via `var(--nk-*)` so a single edit propagates everywhere.

### 2. Select2 CDN (Admin Layout Only)

Admin layout currently lacks Select2. Add these CDN references in the `<head>`:

```html
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
```

And before `@stack('scripts')` in the body:

```html
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
```

> Note: Check if jQuery is already loaded by Tabler. If yes, skip the jQuery CDN line.

### 3. Select2 Auto-Init Script

Same logic for both layouts (app layout already has this; admin layout needs it added):

```javascript
$(function() {
  $('.form-select').not('.no-select2').not('[data-select2-id]').each(function() {
    var $el = $(this);
    var placeholder = $el.data('placeholder') ||
      $el.find('option[value=""]').first().text() || 'Pilih...';
    $el.select2({
      theme: 'bootstrap-5',
      width: '100%',
      placeholder: placeholder,
      allowClear: $el.find('option[value=""]').length > 0
    });
  });

  // Re-init inside modals when shown
  document.querySelectorAll('.modal').forEach(function(modal) {
    modal.addEventListener('shown.bs.modal', function() {
      $(modal).find('.form-select').not('.no-select2').not('[data-select2-id]').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $(modal)
      });
    });
  });
});
```

Key behaviors:
- Targets `.form-select` elements specifically
- Skips elements with `.no-select2` class (opt-out)
- Skips already-initialized elements (`[data-select2-id]`)
- Uses `bootstrap-5` theme for visual consistency
- Re-initializes inside modals with `dropdownParent` to prevent z-index clipping

### 4. Dropdown CSS Overrides (Both Layouts)

```css
/* ── Select2 Unified Styling ── */
.select2-container--bootstrap-5 .select2-selection {
  min-height: var(--nk-height) !important;
  height: var(--nk-height) !important;
  border-radius: var(--nk-radius) !important;
  font-size: 0.8125rem !important;
  display: flex !important;
  align-items: center !important;
}

.select2-container--bootstrap-5.select2-container--focus .select2-selection,
.select2-container--bootstrap-5.select2-container--open .select2-selection {
  border-color: var(--nk-primary) !important;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, .12) !important;
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
  font-size: 0.8125rem !important;
  line-height: var(--nk-height) !important;
}

.select2-container--bootstrap-5 .select2-dropdown {
  border-radius: var(--nk-radius) !important;
  box-shadow: 0 4px 16px rgba(0, 0, 0, .1) !important;
}

.select2-container--bootstrap-5 .select2-results__option--highlighted:not(.select2-results__option--selected) {
  background: rgba(37, 99, 235, .1) !important;
  color: var(--nk-primary) !important;
}

.select2-container--bootstrap-5 .select2-results__option--selected {
  background: var(--nk-primary) !important;
  color: #fff !important;
}
```

**Dark theme additions (App Layout only):**

```css
[data-theme="dark"] .select2-container--bootstrap-5 .select2-selection {
  background: var(--surface) !important;
  border-color: var(--border) !important;
  color: var(--txt) !important;
}

[data-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
  color: var(--txt) !important;
}

[data-theme="dark"] .select2-dropdown {
  background: var(--surface) !important;
  border-color: var(--border) !important;
}

[data-theme="dark"] .select2-search__field {
  background: var(--bg) !important;
  color: var(--txt) !important;
  border-color: var(--border) !important;
}

[data-theme="dark"] .select2-results__option {
  color: var(--txt) !important;
}
```

### 5. Button CSS Overrides (Both Layouts)

```css
/* ── Button Unified Styling ── */
.btn {
  height: var(--nk-height) !important;
  border-radius: var(--nk-radius) !important;
  font-size: 0.8125rem !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 0.3rem !important;
}

.btn-sm {
  height: var(--nk-height-sm) !important;
  font-size: 0.75rem !important;
  padding: 0.25rem 0.5rem !important;
}

.btn-primary {
  background-color: var(--nk-primary) !important;
  border-color: var(--nk-primary) !important;
  color: #fff !important;
}

.btn-primary:hover {
  background-color: color-mix(in srgb, var(--nk-primary) 90%, black) !important;
  border-color: color-mix(in srgb, var(--nk-primary) 90%, black) !important;
}

.btn-secondary {
  background-color: #6b7280 !important;
  border-color: #6b7280 !important;
  color: #fff !important;
}

.btn-secondary:hover {
  background-color: #5b616b !important;
  border-color: #5b616b !important;
}

.btn-danger {
  background-color: #dc2626 !important;
  border-color: #dc2626 !important;
  color: #fff !important;
}

.btn-danger:hover {
  background-color: #c62222 !important;
  border-color: #c62222 !important;
}
```

### 6. Modal CSS Overrides (Both Layouts)

```css
/* ── Modal Unified Styling ── */
.modal-content {
  border-radius: 12px !important;
  box-shadow: 0 8px 32px rgba(0, 0, 0, .18) !important;
  border: none !important;
}

.modal-header {
  padding: 1rem !important;
  border-bottom: 1px solid var(--tblr-border-color, #e6e7e9) !important;
}

.modal-body {
  padding: 1.25rem !important;
}

.modal-footer {
  padding: 0.75rem 1rem !important;
  border-top: 1px solid var(--tblr-border-color, #e6e7e9) !important;
}

.modal-blur .modal-backdrop {
  backdrop-filter: blur(4px) !important;
  -webkit-backdrop-filter: blur(4px) !important;
}

.modal:not(.modal-blur) .modal-backdrop,
.modal-backdrop {
  background: rgba(0, 0, 0, .5) !important;
}
```

**App Layout variant** (uses `--border` variable instead of `--tblr-border-color`):

```css
.modal-header {
  border-bottom-color: var(--border) !important;
}

.modal-footer {
  border-top-color: var(--border) !important;
}

[data-theme="dark"] .modal-content {
  background: var(--surface) !important;
}

[data-theme="dark"] .modal-header {
  border-bottom-color: var(--border) !important;
}

[data-theme="dark"] .modal-footer {
  border-top-color: var(--border) !important;
}
```

### 7. Table Controls CSS (Both Layouts)

**Search input:**

```css
/* ── Table Search Input ── */
.dataTables_wrapper .dataTables_filter input,
.tb-search input,
input[type="search"].form-control {
  height: var(--nk-height) !important;
  min-height: var(--nk-height) !important;
  border-radius: var(--nk-radius) !important;
  font-size: 0.8125rem !important;
  font-family: 'Inter', sans-serif !important;
}

.dataTables_wrapper .dataTables_filter input:focus,
.tb-search input:focus,
input[type="search"].form-control:focus {
  border-color: var(--nk-primary) !important;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, .12) !important;
  outline: none !important;
}
```

**Pagination (shared between both layouts):**

```css
/* ── Table Pagination ── */
.pagination {
  gap: 2px !important;
}

.page-link {
  border-radius: 4px !important;
  font-size: 0.75rem !important;
  min-width: 32px !important;
  text-align: center !important;
  padding: 0.375rem 0.625rem !important;
}

.page-item.active .page-link {
  background-color: var(--nk-primary) !important;
  border-color: var(--nk-primary) !important;
  color: #fff !important;
}

.page-item:not(.active) .page-link:hover {
  background-color: rgba(37, 99, 235, .06) !important;
}

.page-item.disabled .page-link {
  opacity: 0.5 !important;
  cursor: not-allowed !important;
}

/* DataTables paginate */
.dataTables_wrapper .dataTables_paginate .paginate_button {
  border-radius: 4px !important;
  font-size: 0.75rem !important;
  min-width: 32px !important;
  text-align: center !important;
  padding: 0.375rem 0.625rem !important;
  margin: 0 1px !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
  background: var(--nk-primary) !important;
  border-color: var(--nk-primary) !important;
  color: #fff !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
  background: rgba(37, 99, 235, .06) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
  opacity: 0.5 !important;
  cursor: not-allowed !important;
}
```

**Dark theme additions for table controls (App Layout only):**

```css
[data-theme="dark"] .dataTables_wrapper .dataTables_filter input,
[data-theme="dark"] .tb-search input {
  background: var(--surface) !important;
  border-color: var(--border) !important;
  color: var(--txt) !important;
}
```

---

## Data Models

No data model changes. This feature is purely presentational (CSS + client-side JS). No database tables, API endpoints, or server-side logic involved.

---

## Error Handling

| Scenario | Handling |
|----------|----------|
| Select2 CDN fails to load | `$` / `select2` check before init: `if (typeof $.fn.select2 === 'undefined') return;` |
| Element already has Select2 | The `[data-select2-id]` exclusion prevents double-init |
| `.no-select2` class present | Element is explicitly skipped |
| `color-mix()` unsupported (old browsers) | Only affects hover darkening; buttons still render base color. Safari 15+, Chrome 111+, Firefox 113+ all support it. For the admin panel audience (internal staff), this is acceptable. |
| CSS custom properties unsupported | Practically zero risk (IE11 is dead). All target browsers support CSS vars. |

---

## File Change Summary

| File | Changes |
|------|---------|
| `resources/views/layouts/app.blade.php` | Add `:root` tokens + unified component CSS block after existing styles, before `</style>` |
| `resources/views/layouts/app.blade.php` | Update existing Select2 init to target `.form-select` selector specifically |
| `resources/views/admin/layout/app.blade.php` | Add Select2 CDN links in `<head>` |
| `resources/views/admin/layout/app.blade.php` | Add `:root` tokens + unified component CSS block before `@stack('styles')` |
| `resources/views/admin/layout/app.blade.php` | Add jQuery (if not present via Tabler) + Select2 JS CDN + auto-init script before `@stack('scripts')` |

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Design token propagation

*For any* value assigned to the `--nk-primary` CSS custom property, all components that reference it (`.btn-primary` background, Select2 focus border, pagination active background, table search focus border) SHALL render using that exact value without requiring any additional CSS rule changes.

**Validates: Requirements 8.3**

---

## Testing Strategy

Given this feature is purely CSS overrides + a small JS init script, **property-based testing is not appropriate** for the bulk of requirements. The criteria describe declarative CSS values and DOM states, not functions with varied input spaces.

### Recommended test approach:

1. **Visual regression tests** — Screenshot comparison of key component states (button variants, dropdown open/closed, modal, pagination) in both layouts and both themes.

2. **Computed style assertions** (example-based unit tests):
   - Verify `.btn-primary` computes `background-color: rgb(37, 99, 235)`
   - Verify `.select2-selection` computes `height: 34px` and `border-radius: 6px`
   - Verify `.page-item.active .page-link` computes `background-color: rgb(37, 99, 235)`
   - Verify `:root` defines all four `--nk-*` tokens

3. **Integration tests** for Select2 auto-init:
   - Page loads with `.form-select` elements → verify `[data-select2-id]` attribute present
   - `.no-select2` elements → verify NOT initialized
   - Modal shown event → verify nested selects get initialized with `dropdownParent`

4. **Smoke test** for CDN availability:
   - Admin layout HTML contains Select2 CSS/JS CDN links
   - `$.fn.select2` is defined after page load
