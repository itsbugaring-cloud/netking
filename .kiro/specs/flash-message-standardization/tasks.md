# Implementation Plan: Flash Message Standardization

## Overview

Replace three divergent flash message implementations with a single anonymous Blade component `<x-admin-flash />`. The component is self-contained (markup + inline JS), included in both admin layouts, and all duplicate flash rendering is removed from individual views. Inventory controllers are refactored to use the idiomatic `redirect()->with()` pattern.

## Tasks

- [x] 1. Create the `<x-admin-flash />` Blade component
  - [x] 1.1 Create `resources/views/components/admin-flash.blade.php` with type config map and alert markup
    - Define the `$types` PHP array with class, icon (tabler + boxicons), title, and autoDismiss flag for each of the 4 flash types
    - Accept `$dismissAfter` (default 5000) and `$iconSet` (default 'tabler') props
    - Iterate over types; for each type with a non-empty `session($type)` value, render a Tabler alert with: `alert alert-{mapped} alert-dismissible fade show`, `role="alert"`, icon element, title, escaped message text, and `btn-close` button with `data-bs-dismiss="alert"` and `aria-label="Close"`
    - Apply `Str::limit(500)` truncation only for the `warning` type
    - Skip rendering if session value is null, empty, or not a string (`is_string()` guard)
    - Add `data-autodismiss="{{ $dismissAfter }}"` attribute on success/info alerts
    - _Requirements: 1.1, 1.3, 1.4, 1.5, 2.1, 2.2, 2.3, 2.4, 5.1, 5.2, 5.4, 7.1, 7.2, 7.3, 7.4_

  - [x] 1.2 Add inline auto-dismiss JavaScript inside the component
    - Wrap in `@if` so script tag only renders when at least one auto-dismiss alert exists
    - Implement `setTimeout` + `classList.remove('show')` + DOM removal after 300ms fade
    - Implement hover-to-pause: `mouseenter` clears timeout and tracks remaining time, `mouseleave` restarts with remaining duration
    - Use vanilla JS only — no jQuery or external libraries
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [x] 2. Integrate component into both layouts
  - [x] 2.1 Add `<x-admin-flash />` to `resources/views/admin/layout/app.blade.php`
    - Place inside the existing `.container-xl` div, before `@yield('content')`
    - Remove the existing inline `@if(session('success'))` and `@if(session('error'))` alert blocks from the layout
    - _Requirements: 1.2, 4.1_

  - [x] 2.2 Add `<x-admin-flash icon-set="boxicons" />` to `resources/views/layouts/app.blade.php`
    - Place inside the main content area, before `@yield('content')`
    - _Requirements: 1.2, 4.5_

- [x] 3. Checkpoint - Verify component renders correctly
  - Ensure the component renders in both layouts by visiting any admin page with a flash message set. Ask the user if questions arise.

- [x] 4. Remove duplicate flash rendering from IPAM views
  - [x] 4.1 Remove inline flash alert blocks from the 7 IPAM view files
    - Remove `@if(session('success'))` / `@if(session('error'))` blocks from: `admin/ipam/settings.blade.php`, `admin/ipam/subnets/index.blade.php`, `admin/ipam/dashboard.blade.php`, `admin/ipam/routers/index.blade.php`, `admin/ipam/olts/index.blade.php`, `admin/ipam/audit-log.blade.php`, `admin/ipam/routers/show.blade.php`
    - Ensure no other content is accidentally removed — only the flash message blocks
    - _Requirements: 4.2, 4.4, 4.5_

  - [x] 4.2 Remove `nk-flash` HTML and CSS from `resources/views/admin/olts/show.blade.php`
    - Remove the `.nk-flash`, `.nk-flash-success`, `.nk-flash-error`, `.nk-flash-info` CSS class definitions (inline `<style>` block)
    - Remove the corresponding `@if(session('success'))` / `@elseif(session('error'))` / `@elseif(session('info'))` HTML block
    - _Requirements: 4.3_

- [x] 5. Refactor Inventory controllers to use `redirect()->with()`
  - [x] 5.1 Refactor UnitController and QtyController
    - Replace all `session()->flash('type', 'msg'); return redirect(...)` with `return redirect(...)->with('type', 'msg')`
    - Ensure flash type keys are only `success`, `error`, `warning`, or `info`
    - _Requirements: 6.1, 6.2_

  - [x] 5.2 Refactor MasterBarangController and LokasiController
    - Same pattern: replace `session()->flash()` + separate redirect with chained `->with()`
    - _Requirements: 6.1, 6.2_

  - [x] 5.3 Refactor KategoriController and KabelController
    - Same pattern: replace `session()->flash()` + separate redirect with chained `->with()`
    - _Requirements: 6.1, 6.2_

- [x] 6. Checkpoint - Verify all flash messages still work
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 7. Write tests and verification
  - [ ]* 7.1 Write feature tests for flash component rendering
    - Test each flash type renders correct alert classes and structure
    - Test empty/null session values produce no alert markup
    - Test multiple simultaneous flash types render in correct order
    - Test `data-autodismiss` attribute present on success/info, absent on error/warning
    - Test icon class mapping for both `tabler` and `boxicons` icon sets
    - _Requirements: 1.1, 1.3, 1.4, 1.5, 2.1, 2.2, 2.3, 2.4, 3.1, 3.2_

  - [ ]* 7.2 Write property test for structural completeness (Property 1)
    - **Property 1: Structural completeness of rendered flash alerts**
    - Generate random non-empty strings and random flash types; render the component; assert all required structural elements are present (alert classes, role, icon, title, message, close button)
    - **Validates: Requirements 1.4, 2.2, 2.4, 7.1, 7.2, 7.3**

  - [ ]* 7.3 Write property test for warning truncation (Property 2)
    - **Property 2: Warning message truncation**
    - Generate random strings of varying lengths (1–2000 chars); set as `session('warning')`; render the component; assert truncation at 500 chars with ellipsis for long messages, full text for short messages
    - **Validates: Requirements 5.2**

  - [ ]* 7.4 Run grep verification for cleanup completeness
    - Grep `resources/views/admin/` for `session('success')`, `session('error')`, `session('warning')`, `session('info')` — only matches should be in `admin-flash.blade.php`
    - Grep inventory controllers for `session()->flash(` — should return zero matches
    - _Requirements: 4.4, 6.1_

- [x] 8. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- The component is self-contained — no service providers, middleware, or view composers needed
- Both `admin.layout.app` (Tabler icons) and `layouts.app` (Boxicons) are supported via the `icon-set` prop

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["1.2"] },
    { "id": 2, "tasks": ["2.1", "2.2"] },
    { "id": 3, "tasks": ["4.1", "4.2", "5.1", "5.2", "5.3"] },
    { "id": 4, "tasks": ["7.1", "7.2", "7.3", "7.4"] }
  ]
}
```
