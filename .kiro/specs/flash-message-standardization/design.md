# Design Document: Flash Message Standardization

## Overview

This design replaces three divergent flash message implementations (Tabler alerts in admin layout, inline-styled alerts in IPAM views, and custom `nk-flash` in OLT show view) with a single anonymous Blade component `<x-admin-flash />`. The component renders all four flash types (success, error, warning, info) with consistent Tabler styling, auto-dismiss behavior for transient messages, hover-to-pause, fade-out animation, and full accessibility support.

The key challenge is that admin views use two different layouts:
- `admin.layout.app` — standard admin pages (Tabler-based)
- `layouts.app` — IPAM and OLT views (Bootstrap 5 + custom theme)

The component will be included in **both** layouts, replacing all existing inline flash rendering. Since IPAM/OLT views render their own flash messages inside `@section('content')`, removing those and relying on the layout-level component ensures a single source of truth.

### Design Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Component type | Anonymous Blade component (`resources/views/components/admin-flash.blade.php`) | No PHP class needed — pure template logic. Laravel auto-discovers it as `<x-admin-flash />`. |
| Placement strategy | Include in both `admin.layout.app` and `layouts.app` | Both layouts serve admin views; a single include point per layout ensures all admin pages get flash rendering. |
| Auto-dismiss JS | Inline `<script>` inside the component, conditionally rendered | Keeps the component self-contained. Only emits JS when there's actually a flash to dismiss. No dependency on `@stack('scripts')` ordering. |
| Animation | CSS transition (`opacity` + `transform`) toggled via JS class removal | Uses existing Bootstrap `fade show` classes. Removing `show` triggers the CSS transition; after 300ms, the element is removed from DOM. |
| Icon library | Tabler Icons (`ti ti-*`) in `admin.layout.app`, Boxicons (`bx bx-*`) in `layouts.app` | Each layout already loads its respective icon library. The component accepts an optional `icon-set` prop defaulting to context. |
| Truncation | Server-side `Str::limit()` at 500 characters | Prevents excessively long messages from breaking layout. Applied only to the warning type per requirements. |

## Architecture

```mermaid
graph TD
    A[Controller Action] -->|redirect()->with('type', 'msg')| B[Session Flash Store]
    B --> C{Layout renders}
    C -->|admin.layout.app| D["<x-admin-flash />"]
    C -->|layouts.app| D
    D --> E{Check each session key}
    E -->|session('success')| F[Render success alert]
    E -->|session('error')| G[Render error alert]
    E -->|session('warning')| H[Render warning alert]
    E -->|session('info')| I[Render info alert]
    F & G & H & I --> J[Inline JS: auto-dismiss for success/info]
    J --> K[User sees flash message]
```

The component is stateless and reads directly from the session on each page load. No middleware, service provider, or view composer is required.

## Components and Interfaces

### 1. `<x-admin-flash />` — Anonymous Blade Component

**File:** `resources/views/components/admin-flash.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `dismissAfter` | `int` | `5000` | Auto-dismiss delay in ms for success/info types |
| `iconSet` | `string` | `'tabler'` | Icon library to use: `'tabler'` or `'boxicons'` |

**Behavior:**
- Iterates over `['success', 'error', 'warning', 'info']` in that order
- For each type with a non-empty session value, renders a Tabler-style alert
- success/info alerts get a `data-autodismiss` attribute triggering the JS timer
- warning/error alerts remain until manually dismissed
- All alerts include `role="alert"`, `aria-label="Close"` on the button, and are keyboard-accessible

**Usage in `admin.layout.app`:**
```blade
<div class="container-xl">
    <x-admin-flash />
    @yield('content')
</div>
```

**Usage in `layouts.app`:**
```blade
<main class="main">
    <x-admin-flash icon-set="boxicons" />
    @yield('content')
</main>
```

### 2. Type Configuration Map

Internal to the component, a PHP array defines per-type metadata:

```php
@php
$types = [
    'success' => [
        'class' => 'alert-success',
        'icon_tabler' => 'ti ti-check',
        'icon_box' => 'bx bx-check-circle',
        'title' => 'Berhasil!',
        'autoDismiss' => true,
    ],
    'error' => [
        'class' => 'alert-danger',
        'icon_tabler' => 'ti ti-alert-circle',
        'icon_box' => 'bx bx-error-circle',
        'title' => 'Error!',
        'autoDismiss' => false,
    ],
    'warning' => [
        'class' => 'alert-warning',
        'icon_tabler' => 'ti ti-alert-triangle',
        'icon_box' => 'bx bx-error',
        'title' => 'Perhatian!',
        'autoDismiss' => false,
    ],
    'info' => [
        'class' => 'alert-info',
        'icon_tabler' => 'ti ti-info-circle',
        'icon_box' => 'bx bx-info-circle',
        'title' => 'Info',
        'autoDismiss' => true,
    ],
];
@endphp
```

### 3. Auto-Dismiss JavaScript (inline)

```javascript
document.querySelectorAll('[data-autodismiss]').forEach(function(el) {
    var delay = parseInt(el.getAttribute('data-autodismiss')) || 5000;
    var timer = null;
    var remaining = delay;
    var startTime = null;

    function startTimer() {
        startTime = Date.now();
        timer = setTimeout(function() { dismiss(el); }, remaining);
    }

    function dismiss(element) {
        element.classList.remove('show');
        setTimeout(function() { element.remove(); }, 300);
    }

    el.addEventListener('mouseenter', function() {
        clearTimeout(timer);
        remaining -= (Date.now() - startTime);
    });

    el.addEventListener('mouseleave', function() {
        startTimer();
    });

    startTimer();
});
```

### 4. Controller Refactoring Pattern

**Before (inventory controllers):**
```php
session()->flash('success', 'Unit berhasil ditambahkan.');
return redirect()->route('admin.inventory.units.show', $unit);
```

**After:**
```php
return redirect()->route('admin.inventory.units.show', $unit)
    ->with('success', 'Unit berhasil ditambahkan.');
```

## Data Models

No database schema changes are required. The feature operates entirely on Laravel's session flash mechanism.

**Session keys consumed by the component:**

| Key | Type | Max Length | Behavior |
|-----|------|-----------|----------|
| `success` | `string\|null` | Unlimited | Rendered as green alert, auto-dismisses |
| `error` | `string\|null` | Unlimited | Rendered as red alert, manual dismiss only |
| `warning` | `string\|null` | 500 chars (truncated) | Rendered as yellow alert, manual dismiss only |
| `info` | `string\|null` | Unlimited | Rendered as blue alert, auto-dismisses |

**Reserved non-flash session keys** (not rendered by the component):
- `import_errors` — array of import validation errors
- `import_billing_errors` — array of billing import errors

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Structural completeness of rendered flash alerts

*For any* valid flash type (success, error, warning, info) and *for any* non-empty message string, the rendered component output SHALL contain:
- A container element with classes `alert`, `alert-{mapped_type}`, `alert-dismissible`, `fade`, `show`
- The attribute `role="alert"` on the container
- An icon element with the correct class for the flash type
- A title string matching the type's configured title
- The message text (or its truncated form)
- A `<button>` element with class `btn-close`, attribute `data-bs-dismiss="alert"`, and attribute `aria-label="Close"`

**Validates: Requirements 1.4, 2.2, 2.4, 7.1, 7.2, 7.3**

### Property 2: Warning message truncation

*For any* message string set in `session('warning')`:
- If the string length is ≤ 500 characters, the rendered output SHALL contain the full message text
- If the string length is > 500 characters, the rendered output SHALL contain exactly the first 500 characters followed by an ellipsis (`…`), and SHALL NOT contain the full original string

**Validates: Requirements 5.2**

## Error Handling

| Scenario | Handling |
|----------|----------|
| Session value is `null` or empty string | Component skips rendering for that type (no empty alert box) |
| Session value is non-string (array, object) | Component skips rendering — `is_string()` guard prevents type errors |
| Session value contains HTML/XSS | Blade `{{ }}` auto-escapes all output; raw HTML is never rendered |
| Multiple flash types set simultaneously | All are rendered in defined order (success → error → warning → info) |
| Layout missing the component include | Flash messages won't display — detected by smoke test grep |
| JavaScript disabled | Alerts still render and are dismissible via Bootstrap's CSS-only close button pattern; auto-dismiss won't fire but messages remain usable |

## Testing Strategy

### Unit Tests (Feature Tests)

Laravel feature tests using `$this->get()` / `$this->post()` with session assertions:

1. **Render each flash type** — Set each session key, render a view, assert the alert HTML is present with correct classes
2. **Empty/null values** — Set empty session values, assert no alert markup in response
3. **Multiple simultaneous types** — Set all 4 keys, assert correct order in HTML
4. **Truncation** — Set a 600-char warning message, assert output contains only 500 chars + ellipsis
5. **Auto-dismiss attribute** — Assert `data-autodismiss` present on success/info, absent on error/warning
6. **Icon mapping** — Assert correct icon class per type per icon set

### Property-Based Tests

Using [Pest](https://pestphp.com/) with a property-testing plugin or a custom generator approach:

- **Library:** `pestphp/pest` with inline data providers generating random strings
- **Minimum iterations:** 100 per property
- **Tag format:** `Feature: flash-message-standardization, Property {N}: {title}`

**Property 1 test:** Generate random non-empty strings (varying length, unicode, special chars) and random flash types. Render the component with that session data. Assert all structural elements are present.

**Property 2 test:** Generate random strings of varying lengths (1–2000 chars). Set as `session('warning')`. Render the component. If length > 500, assert truncation. If ≤ 500, assert full text present.

### Smoke Tests

- Grep `resources/views/admin/` for `session('success')`, `session('error')`, `session('warning')`, `session('info')` — only matches in `admin-flash.blade.php`
- Grep inventory controllers for `session()->flash(` — should return zero matches after refactoring

### Integration / Browser Tests (manual or Dusk)

- Auto-dismiss fires after 5 seconds on success/info
- Hover pauses the timer
- Fade-out animation plays before DOM removal
- Keyboard navigation: Tab to close button, Enter/Space to dismiss
