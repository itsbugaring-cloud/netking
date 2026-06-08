# Implementation Plan: UI Component Unification

## Overview

Inject unified CSS design tokens and component overrides into both `layouts/app.blade.php` and `admin/layout/app.blade.php`, add Select2 CDN to the admin layout, and wire up the Select2 auto-init script. All changes are CSS blocks and a small jQuery snippet — no build tools or new Blade components.

## Tasks

- [ ] 1. Add design tokens and unified CSS to App Layout
  - [ ] 1.1 Add `:root` CSS tokens and all component CSS overrides to `resources/views/layouts/app.blade.php`
    - Add a new `<style>` block after existing styles containing:
      - `:root` with `--nk-primary`, `--nk-height`, `--nk-height-sm`, `--nk-radius`
      - Select2 dropdown CSS overrides (including dark theme variants)
      - Button CSS overrides (`.btn`, `.btn-sm`, `.btn-primary`, `.btn-secondary`, `.btn-danger` + hovers)
      - Modal CSS overrides (including dark theme variants using `--border` variable)
      - Table search input CSS overrides (including dark theme)
      - Table pagination CSS overrides (Laravel paginator + DataTables)
    - _Requirements: 8.1, 8.3, 8.4, 2.1–2.8, 3.1–3.10, 4.1–4.9, 5.1–5.5, 7.1–7.8_

  - [ ] 1.2 Update existing Select2 init script in `resources/views/layouts/app.blade.php`
    - Modify the existing Select2 initialization to target `.form-select` elements
    - Add `.not('.no-select2').not('[data-select2-id]')` guards
    - Add modal re-init logic with `dropdownParent` on `shown.bs.modal`
    - Add safety check: `if (typeof $.fn.select2 === 'undefined') return;`
    - _Requirements: 1.1, 1.3_

- [ ] 2. Add design tokens, unified CSS, Select2 CDN, and init script to Admin Layout
  - [ ] 2.1 Add Select2 CDN links to `resources/views/admin/layout/app.blade.php` head
    - Add Select2 CSS CDN (`select2.min.css`)
    - Add Select2 Bootstrap 5 theme CSS CDN (`select2-bootstrap-5-theme.min.css`)
    - Check if jQuery is already loaded by Tabler; if not, add jQuery CDN
    - Add Select2 JS CDN before `@stack('scripts')`
    - _Requirements: 1.4_

  - [ ] 2.2 Add `:root` CSS tokens and all component CSS overrides to `resources/views/admin/layout/app.blade.php`
    - Add a new `<style>` block before `@stack('styles')` containing:
      - `:root` with `--nk-primary`, `--nk-height`, `--nk-height-sm`, `--nk-radius`
      - Select2 dropdown CSS overrides (no dark theme needed for admin)
      - Button CSS overrides
      - Modal CSS overrides (using `--tblr-border-color` variable)
      - Table search input CSS overrides
      - Table pagination CSS overrides
    - _Requirements: 8.2, 8.3, 8.4, 2.1–2.4, 2.6–2.8, 3.1–3.10, 4.1–4.7, 4.9, 5.1–5.4, 7.1–7.8_

  - [ ] 2.3 Add Select2 auto-init script to `resources/views/admin/layout/app.blade.php`
    - Add `<script>` block before `@stack('scripts')` with:
      - Safety check for `$.fn.select2`
      - Auto-init on `.form-select` elements with `.not('.no-select2').not('[data-select2-id]')` guards
      - Bootstrap-5 theme, `width: '100%'`, placeholder detection, `allowClear` logic
      - Modal re-init on `shown.bs.modal` with `dropdownParent`
    - _Requirements: 1.2, 1.3_

- [ ] 3. Checkpoint - Verify unified styling
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- This feature is purely CSS overrides + a small JS init script — no build tools or server-side changes
- All component styles reference `var(--nk-*)` tokens so future changes propagate from one place
- The `.no-select2` class provides an opt-out escape hatch for specific selects
- `color-mix()` for hover states requires modern browsers (Safari 15+, Chrome 111+, Firefox 113+) — acceptable for internal admin use
- Dark theme CSS only applies to App Layout (Admin Layout uses Tabler's light theme)

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "2.1"] },
    { "id": 1, "tasks": ["1.2", "2.2"] },
    { "id": 2, "tasks": ["2.3"] }
  ]
}
```
