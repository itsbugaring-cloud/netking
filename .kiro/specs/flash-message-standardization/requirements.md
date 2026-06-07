# Requirements Document

## Introduction

The Netking admin panel currently renders flash messages using three different approaches: Tabler alerts in the main layout, inline-styled alerts copy-pasted in IPAM views, and a custom `nk-flash` component in the OLT show view. Additionally, `warning` and `info` flash types are set by controllers but not universally rendered. This feature standardizes all flash message rendering into a single reusable Blade component, ensures all four flash types (success, error, warning, info) are displayed consistently, and replaces all duplicated flash rendering code across the admin panel.

## Glossary

- **Flash_Component**: The single reusable Blade component (`x-admin-flash`) responsible for rendering all session flash messages in the admin panel
- **Flash_Type**: One of the four supported message categories: `success`, `error`, `warning`, `info`
- **Admin_Layout**: The main admin layout template (`resources/views/admin/layout/app.blade.php`) that wraps all admin views
- **Auto_Dismiss**: Behavior where a flash message automatically fades out and removes itself from the DOM after a configurable timeout
- **Flash_Session_Key**: A Laravel session key used to pass a message string from a controller to the next rendered view (e.g., `session('success')`)

## Requirements

### Requirement 1: Centralized Flash Component

**User Story:** As a developer, I want a single reusable Blade component for flash messages, so that I do not have to copy-paste flash rendering logic into individual views.

#### Acceptance Criteria

1. THE Flash_Component SHALL render a dismissible alert for each Flash_Type using the following mapping: `success` as `alert-success`, `error` as `alert-danger`, `warning` as `alert-warning`, and `info` as `alert-info`
2. THE Flash_Component SHALL be included once in the Admin_Layout, before the `@yield('content')` directive, inside the same `container-xl` element that wraps the page content
3. WHEN multiple Flash_Session_Keys are present simultaneously, THE Flash_Component SHALL render one alert for each present Flash_Type, in the order: success, error, warning, info
4. WHEN a Flash_Session_Key is present, THE Flash_Component SHALL display the alert with an icon, a title indicating the Flash_Type, the session message text, and a close button that dismisses the alert
5. IF a Flash_Session_Key is present but its value is empty or null, THEN THE Flash_Component SHALL not render an alert for that Flash_Type

### Requirement 2: Consistent Visual Styling

**User Story:** As a user, I want flash messages to look the same on every page, so that I can immediately recognize the message type regardless of which page I am on.

#### Acceptance Criteria

1. THE Flash_Component SHALL render each Flash_Type with a distinct background color, border color, icon, and text color that remains identical across all admin pages
2. THE Flash_Component SHALL use Tabler alert markup (`alert alert-{type} alert-dismissible fade show`) for consistent styling with the admin UI kit
3. THE Flash_Component SHALL display a Tabler icon (`ti ti-*` class prefix) appropriate to each Flash_Type: `ti-check` for success, `ti-alert-circle` for error, `ti-alert-triangle` for warning, `ti-info-circle` for info
4. THE Flash_Component SHALL include a dismissible close button on each alert using `btn-close` with `data-bs-dismiss="alert"`

### Requirement 3: Auto-Dismiss Behavior

**User Story:** As a user, I want success and info messages to disappear automatically after a few seconds, so that they do not clutter the screen after I have read them.

#### Acceptance Criteria

1. WHEN a success or info flash message is rendered, THE Flash_Component SHALL start an auto-dismiss countdown of 5 seconds, after which the message is removed from view
2. WHEN a warning or error flash message is rendered, THE Flash_Component SHALL keep the message visible until the user manually dismisses it via the close button
3. WHEN the user hovers over an auto-dismissing message during the countdown, THE Flash_Component SHALL pause the countdown timer and resume it when the pointer leaves the message
4. WHEN a flash message is dismissed (either automatically or manually), THE Flash_Component SHALL animate the removal with a fade-out transition lasting 300 milliseconds before removing the element from the DOM
5. THE Flash_Component SHALL implement auto-dismiss using vanilla JavaScript (setTimeout and classList manipulation) or Bootstrap's native Alert API, without jQuery or additional external libraries

### Requirement 4: Remove Duplicate Flash Rendering

**User Story:** As a developer, I want to remove all copy-pasted flash rendering code from individual views, so that future changes only require editing one file.

#### Acceptance Criteria

1. WHEN the Flash_Component is integrated into the Admin_Layout, THE Admin_Layout SHALL remove its existing inline success and error alert markup (the `@if(session('success'))` and `@if(session('error'))` blocks within the `.container-xl` div) so that no flash-rendering HTML remains in `admin/layout/app.blade.php`
2. WHEN the Flash_Component is integrated, THE IPAM views (settings, subnets/index, dashboard, routers/index, olts/index, audit-log, routers/show) SHALL each have their inline-styled `@if(session('success'))` and `@if(session('error'))` alert blocks removed entirely, with zero session-flash rendering markup remaining in those 7 files
3. WHEN the Flash_Component is integrated, THE OLT show view (`admin/olts/show.blade.php`) SHALL remove its `.nk-flash`, `.nk-flash-success`, `.nk-flash-error`, `.nk-flash-info` CSS class definitions and the corresponding `@if(session('success'))` / `@elseif(session('error'))` / `@elseif(session('info'))` HTML block
4. THE Flash_Component SHALL be the only location in the admin panel that renders session flash messages, verified by confirming that a text search for `session('success')`, `session('error')`, and `session('info')` within view files under `resources/views/admin/` returns matches only inside the Flash_Component file
5. IF a view previously rendering its own flash messages is loaded after the Flash_Component integration, THEN THE system SHALL still display flash messages to the user via the centralized Flash_Component in the layout, with no visual regression

### Requirement 5: Support Warning Flash Type

**User Story:** As a user, I want to see warning messages when an operation partially succeeds, so that I know something needs my attention even though the action completed.

#### Acceptance Criteria

1. WHEN a `warning` Flash_Session_Key is present and contains a non-empty string, THE Flash_Component SHALL render a warning-styled alert using the Tabler `alert-warning` class which applies a yellow/amber color scheme visually distinguishable from success, error, and info alerts
2. WHEN a `warning` Flash_Session_Key is present, THE Flash_Component SHALL render the warning message text from `session('warning')` inside the alert body, displaying up to 500 characters and truncating any content beyond that limit with an ellipsis
3. WHEN CustomersImportController sets a `warning` flash via `redirect()->with('warning', ...)`, THE Flash_Component SHALL display the warning alert on the redirected page alongside any other simultaneously present Flash_Types
4. IF the `warning` Flash_Session_Key is present but contains an empty string or null value, THEN THE Flash_Component SHALL NOT render the warning alert

### Requirement 6: Standardize Controller Flash-Setting Pattern

**User Story:** As a developer, I want all controllers to use the same pattern for setting flash messages, so that the codebase is consistent and predictable.

#### Acceptance Criteria

1. THE Inventory controllers (UnitController, QtyController, MasterBarangController, LokasiController, KategoriController, KabelController) SHALL use `redirect()->route(...)->with('type', 'message')` or `redirect()->back()->with('type', 'message')` instead of calling `session()->flash()` followed by a separate `redirect()` statement
2. THE Admin controllers (all controllers under `App\Http\Controllers\Admin` namespace, including Inventory sub-namespace) SHALL use only the four standard flash type keys: `success`, `error`, `warning`, `info` for single scalar string messages
3. WHEN a controller needs to pass non-scalar data (arrays or objects) to the redirect target, THE controller SHALL store it under a descriptive session key (e.g., `import_errors`, `import_billing_errors`) distinct from the four standard flash type keys

### Requirement 7: Accessibility

**User Story:** As a user with assistive technology, I want flash messages to be announced by screen readers, so that I am aware of operation outcomes without relying on visual cues.

#### Acceptance Criteria

1. THE Flash_Component SHALL include `role="alert"` on each flash message container so that the message content is announced by screen readers immediately upon rendering
2. THE Flash_Component SHALL include `aria-label="Close"` on the close button element for each alert
3. THE Flash_Component SHALL render the close control as a `<button>` element so that it is keyboard-focusable and activatable via both Enter and Space keys without additional scripting
4. WHEN a flash message is rendered, THE Flash_Component SHALL ensure the `role="alert"` attribute is present on the container before the message text content is inserted into the DOM
