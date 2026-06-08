# Requirements Document

## Introduction

Unify the visual styling and behavior of core UI components (dropdowns, buttons, modals, table controls) across both application layouts: `layouts.app` (dark theme, Bootstrap 5) and `admin.layout.app` (Tabler, white theme). The goal is a consistent design language with shared dimensions (34px default height, 6px border-radius) and the primary color #2563eb, delivered entirely through CSS overrides and CDN-loaded libraries without a build system.

## Glossary

- **App_Layout**: The main application layout at `resources/views/layouts/app.blade.php`, using Bootstrap 5 with a dark theme and Select2 already loaded via CDN.
- **Admin_Layout**: The admin panel layout at `resources/views/admin/layout/app.blade.php`, using Tabler UI framework with a white/light theme.
- **Dropdown_Component**: A `<select>` element enhanced by Select2 (jQuery plugin) for searchable, styled dropdown behavior.
- **Button_Component**: Any HTML element using `.btn` class for clickable actions.
- **Modal_Component**: A Bootstrap 5 or Tabler modal dialog overlay (`modal-blur` or standard `.modal`).
- **Table_Control**: The search input, filter dropdown, and pagination elements associated with DataTables or manual table layouts.
- **Design_Token**: A shared CSS custom property defining spacing, color, or sizing values.
- **Primary_Color**: The value #2563eb used as the main accent color across both layouts.
- **Default_Height**: 34px, the standard interactive element height.
- **Small_Height**: 30px, the compact interactive element height for `-sm` variants.
- **Border_Radius**: 6px, the standard corner rounding for interactive elements.

## Requirements

### Requirement 1: Dropdown Auto-Initialization

**User Story:** As a developer, I want all `.form-select` elements to automatically initialize as Select2 dropdowns, so that I do not need to manually call Select2 on every page.

#### Acceptance Criteria

1. WHEN a page using App_Layout finishes loading, THE Dropdown_Component SHALL initialize Select2 on every `<select>` element with the class `.form-select` that has not already been initialized.
2. WHEN a page using Admin_Layout finishes loading, THE Dropdown_Component SHALL initialize Select2 on every `<select>` element with the class `.form-select` that has not already been initialized.
3. WHEN Select2 is auto-initialized, THE Dropdown_Component SHALL apply the Bootstrap 5 theme (`select2-bootstrap-5-theme`) as the default theme parameter.
4. IF Select2 CDN script is not loaded in Admin_Layout, THEN THE Admin_Layout SHALL include the Select2 CSS and JS CDN references in the document head.

### Requirement 2: Dropdown Consistent Styling

**User Story:** As a user, I want all dropdowns to look the same across both layouts, so that the interface feels cohesive regardless of which section I am in.

#### Acceptance Criteria

1. THE Dropdown_Component SHALL render the Select2 selection container at Default_Height (34px) with vertically centered text.
2. THE Dropdown_Component SHALL apply Border_Radius (6px) to the Select2 selection container.
3. WHEN a Dropdown_Component receives focus, THE Dropdown_Component SHALL display a border color of Primary_Color and a subtle box-shadow of `0 0 0 2px rgba(37,99,235,.12)`.
4. THE Dropdown_Component SHALL use font-size of 0.8125rem for the selection text.
5. WHILE App_Layout is in dark theme mode, THE Dropdown_Component SHALL use the dark theme background, border, and text color CSS variables for the selection container, dropdown panel, and search field.
6. THE Dropdown_Component SHALL render the dropdown results panel with Border_Radius (6px) and a subtle box-shadow.
7. WHEN a result option is highlighted, THE Dropdown_Component SHALL apply a light-blue background (`rgba(37,99,235,.1)`) with Primary_Color text.
8. WHEN a result option is selected, THE Dropdown_Component SHALL apply Primary_Color as the background with white text.

### Requirement 3: Button Standardization

**User Story:** As a developer, I want a single, predictable button system, so that I can use consistent classes without guessing which variant applies.

#### Acceptance Criteria

1. THE Button_Component SHALL render at Default_Height (34px) using `display: inline-flex` with vertically centered content.
2. THE Button_Component SHALL apply Border_Radius (6px) to all button variants.
3. THE Button_Component SHALL use font-size of 0.8125rem for default buttons.
4. WHEN a Button_Component has the class `.btn-sm`, THE Button_Component SHALL render at Small_Height (30px) with font-size 0.75rem and reduced padding.
5. WHEN a Button_Component has the class `.btn-primary`, THE Button_Component SHALL use Primary_Color (#2563eb) as background-color with white text.
6. WHEN a Button_Component has the class `.btn-secondary`, THE Button_Component SHALL use a gray background (#6b7280) with white text.
7. WHEN a Button_Component has the class `.btn-danger`, THE Button_Component SHALL use a red background (#dc2626) with white text.
8. WHEN a Button_Component is hovered, THE Button_Component SHALL darken the background color by 10% relative to the base variant color.
9. THE Button_Component SHALL apply the same height, border-radius, and font-size rules in both App_Layout and Admin_Layout.
10. THE Button_Component SHALL render with a gap of 0.3rem between icon and label content when both are present.

### Requirement 4: Modal Unification

**User Story:** As a user, I want modals to look and behave the same whether I am in the admin panel or the main app, so the experience feels seamless.

#### Acceptance Criteria

1. THE Modal_Component SHALL apply Border_Radius of 12px to the `.modal-content` element in both App_Layout and Admin_Layout.
2. THE Modal_Component SHALL apply a box-shadow of `0 8px 32px rgba(0,0,0,.18)` to the `.modal-content` element.
3. THE Modal_Component SHALL render the `.modal-content` element with no visible border (`border: none`).
4. THE Modal_Component SHALL render the `.modal-header` with a bottom border of `1px solid` using the layout-appropriate border-color variable.
5. THE Modal_Component SHALL render the `.modal-footer` with a top border of `1px solid` using the layout-appropriate border-color variable.
6. WHEN a modal is opened using the `modal-blur` class (Tabler pattern), THE Modal_Component SHALL apply a backdrop blur filter to the page background.
7. WHEN a modal is opened without the `modal-blur` class (Bootstrap pattern), THE Modal_Component SHALL apply a semi-transparent dark backdrop (`rgba(0,0,0,.5)`).
8. WHILE App_Layout is in dark theme mode, THE Modal_Component SHALL use dark-theme surface background and border-color variables for `.modal-content`, `.modal-header`, and `.modal-footer`.
9. THE Modal_Component SHALL apply consistent padding of 1rem to `.modal-header`, 1.25rem to `.modal-body`, and 0.75rem 1rem to `.modal-footer` in both layouts.

### Requirement 5: Table Search Input Styling

**User Story:** As a user, I want table search inputs to match the overall form styling, so they do not appear visually disconnected from other inputs.

#### Acceptance Criteria

1. THE Table_Control search input SHALL render at Default_Height (34px) with Border_Radius (6px).
2. THE Table_Control search input SHALL use font-size of 0.8125rem and font-family Inter.
3. WHEN a Table_Control search input receives focus, THE Table_Control SHALL display a border color of Primary_Color with box-shadow `0 0 0 2px rgba(37,99,235,.12)`.
4. THE Table_Control search input SHALL apply the same styling rules in both App_Layout and Admin_Layout.
5. WHILE App_Layout is in dark theme mode, THE Table_Control search input SHALL use dark-theme background and border-color variables.

### Requirement 6: Table Filter Dropdown Styling

**User Story:** As a user, I want table filter dropdowns to match the custom dropdown styling, so that the filtering experience is consistent.

#### Acceptance Criteria

1. THE Table_Control filter dropdown SHALL render as a Select2 Dropdown_Component following the same styling rules defined in Requirement 2.
2. WHEN a Table_Control filter dropdown is placed adjacent to a search input, THE Table_Control filter dropdown SHALL render at the same Default_Height (34px) to maintain visual alignment.
3. THE Table_Control filter dropdown SHALL apply consistent styling in both App_Layout and Admin_Layout.

### Requirement 7: Table Pagination Styling

**User Story:** As a user, I want pagination buttons to look the same across all tables in both layouts, so that navigation is predictable.

#### Acceptance Criteria

1. THE Table_Control pagination buttons SHALL render with a border-radius of 4px and font-size of 0.75rem.
2. THE Table_Control pagination buttons SHALL use a minimum width of 32px with centered text.
3. WHEN a pagination button represents the active page, THE Table_Control pagination button SHALL use Primary_Color as background with white text and matching border-color.
4. WHEN a pagination button is hovered (not active), THE Table_Control pagination button SHALL apply a light background highlight.
5. WHEN a pagination button is disabled, THE Table_Control pagination button SHALL display at 50% opacity with a `not-allowed` cursor.
6. THE Table_Control pagination buttons SHALL apply the same styling for both Laravel paginator markup and DataTables paginate buttons.
7. THE Table_Control pagination buttons SHALL apply consistent styling in both App_Layout and Admin_Layout.
8. THE Table_Control pagination container SHALL display buttons with a gap of 2px between each button.

### Requirement 8: Shared Design Tokens

**User Story:** As a developer, I want shared CSS variables for component dimensions and colors, so that future changes only require updating one place.

#### Acceptance Criteria

1. THE App_Layout SHALL define CSS custom properties for Primary_Color, Default_Height, Small_Height, and Border_Radius on the `:root` selector.
2. THE Admin_Layout SHALL define the same CSS custom properties on the `:root` selector with identical values.
3. WHEN a Design_Token value is updated, THE Button_Component, Dropdown_Component, Modal_Component, and Table_Control SHALL reflect the updated value without additional style changes.
4. THE Design_Token definitions SHALL use the naming convention `--nk-` prefix (e.g., `--nk-primary`, `--nk-height`, `--nk-height-sm`, `--nk-radius`).
