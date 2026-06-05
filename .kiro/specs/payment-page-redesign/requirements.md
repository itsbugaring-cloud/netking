# Requirements Document

## Introduction

Redesign the public payment page (`/bayar`) to adopt the Tabler UI framework styling used in the admin panel, replacing the current custom CSS approach. All existing functionality must be preserved: customer code search, invoice display, payment proof upload, and payment information (bank accounts and QRIS). The goal is visual consistency with the admin panel while maintaining the page's public accessibility (no authentication required).

## Glossary

- **Payment_Page**: The public-facing `/bayar` page rendered by `payments/public.blade.php` that allows customers to check invoices and submit payment proofs without logging in.
- **Tabler_Framework**: The CSS/JS UI framework (`@tabler/core`) used by the admin panel, providing cards, forms, buttons, alerts, badges, typography, and layout utilities.
- **Customer_Code**: A unique alphanumeric identifier (e.g., NK000123) assigned to each customer, used to look up their invoices.
- **Invoice**: A billing record containing amount, due date, payment status, and optional payment proof metadata.
- **Payment_Proof**: An image file (JPG, PNG, or WEBP, max 5 MB) uploaded by the customer as evidence of payment.
- **Payment_Settings**: Configuration data including bank account details (name, number, holder) and QRIS image/label managed via application settings.
- **Admin_Panel**: The authenticated admin interface using Tabler framework styling as defined in `admin/layout/app.blade.php`.

## Requirements

### Requirement 1: Tabler Framework Adoption

**User Story:** As a product owner, I want the payment page to use the Tabler UI framework, so that it has a consistent look and feel with the admin panel.

#### Acceptance Criteria

1. THE Payment_Page SHALL load the Tabler CSS (`@tabler/core` stylesheet) and Tabler Icons webfont from the same CDN source used by the Admin_Panel.
2. THE Payment_Page SHALL use the Inter font family with the same font-feature-settings as the Admin_Panel typography configuration.
3. THE Payment_Page SHALL contain zero custom CSS variables and zero custom component styles defined inline, replacing all with Tabler utility classes and components.
4. THE Payment_Page SHALL use Tabler card components (`.card`, `.card-header`, `.card-body`) for all content sections.
5. THE Payment_Page SHALL use Tabler form components (`.form-control`, `.form-select`, `.btn`) for all interactive elements.
6. THE Payment_Page SHALL use Tabler alert components (`.alert`, `.alert-success`, `.alert-danger`, `.alert-warning`) for all feedback messages.
7. THE Payment_Page SHALL use Tabler badge components (`.badge`) for invoice status indicators.
8. THE Payment_Page SHALL use Tabler Icons (`ti ti-*`) in place of Boxicons (`bx bx-*`) for all iconography.

### Requirement 2: Page Layout Structure

**User Story:** As a customer, I want the payment page to feel well-organized and professional, so that I can trust the payment process.

#### Acceptance Criteria

1. THE Payment_Page SHALL use Tabler's `container-xl` class as the outermost content wrapper, matching the Admin_Panel's container style.
2. THE Payment_Page SHALL display a page header section containing the Netking branding (icon and title), page title "Pembayaran Netking", and a one-sentence description of the page purpose.
3. THE Payment_Page SHALL arrange content in a responsive grid using Tabler's `row` and `col-md-*`/`col-lg-*` classes that collapses to a single column on viewports below 768px.
4. THE Payment_Page SHALL maintain the following content order from top to bottom: page header, search form card with quick-guide highlights, payment methods sidebar card, customer data and invoice list card, upload form card, payment guide card, and FAQ card.
5. WHILE the viewport width is 768px or greater, THE Payment_Page SHALL display the search/invoice column at approximately 7/12 width and the payment methods column at approximately 5/12 width using Tabler grid classes.

### Requirement 3: Customer Code Search

**User Story:** As a customer, I want to search for my invoices using my customer code, so that I can view and pay my bills.

#### Acceptance Criteria

1. THE Payment_Page SHALL display a search form with a text input for Customer_Code (maximum 32 characters) and a submit button.
2. WHEN the customer submits a Customer_Code that matches an existing record (case-insensitive, leading/trailing whitespace ignored), THE Payment_Page SHALL display the customer's name, Customer_Code, area, and package information.
3. IF the customer submits a Customer_Code that does not match any record, THEN THE Payment_Page SHALL display an error message indicating the customer ID was not found.
4. IF the customer submits the search form with an empty Customer_Code, THEN THE Payment_Page SHALL remain on the search form without displaying customer information or an error message.
5. THE Payment_Page SHALL preserve the submitted Customer_Code value in the search input after form submission.
6. THE Payment_Page SHALL submit the search form via GET request to the payment page route with customer_code as the query parameter.

### Requirement 4: Invoice Display

**User Story:** As a customer, I want to see my unpaid invoices clearly listed, so that I know which bills to pay and how much I owe.

#### Acceptance Criteria

1. WHEN a valid customer is found with unpaid invoices, THE Payment_Page SHALL list each unpaid Invoice ordered by due date ascending, showing the invoice number, due date, prorated indicator (if applicable), and amount formatted in Indonesian Rupiah (e.g., "Rp 150.000").
2. WHEN a valid customer is found with no unpaid invoices, THE Payment_Page SHALL display a success message indicating no active invoices exist.
3. WHEN multiple unpaid invoices exist, THE Payment_Page SHALL display a warning alert informing the customer to select which invoice to pay.
4. THE Payment_Page SHALL display a status badge for each Invoice indicating its state: "Belum Lunas" (unpaid, due date not yet passed), "Jatuh Tempo" (due date has passed), or "Menunggu Review" (payment proof submitted and awaiting admin review).
5. WHEN an invoice has a rejected payment proof, THE Payment_Page SHALL display the rejection reason text alongside the invoice details.
6. THE Payment_Page SHALL provide a selection button for each invoice that navigates to the same page with the invoice ID appended as a query parameter.
7. WHEN only one unpaid invoice exists, THE Payment_Page SHALL auto-select that invoice and immediately display the payment upload form without requiring the customer to manually select it.
8. IF an invoice has a rejected payment proof, THEN THE Payment_Page SHALL display the status badge as "Belum Lunas" or "Jatuh Tempo" based on the due date, rather than "Menunggu Review".

### Requirement 5: Payment Method Information Display

**User Story:** As a customer, I want to see the official bank account numbers and QRIS code, so that I can transfer money to the correct destination.

#### Acceptance Criteria

1. THE Payment_Page SHALL display each configured bank account (where the account_number field is non-empty) showing the bank name, account number, and account holder name, rendered in sequential order.
2. WHERE a QRIS image URL is configured and non-empty, THE Payment_Page SHALL display the QRIS label text above a clickable image (with alt text matching the QRIS label) that opens the full-size image in a new browser tab.
3. WHERE QRIS notes are configured and non-empty, THE Payment_Page SHALL display the notes text directly below the QRIS image within the same payment method section.
4. THE Payment_Page SHALL display the general payment instruction note from Payment_Settings below the bank account list and QRIS section.
5. IF no bank accounts are configured (all account_number fields are empty), THEN THE Payment_Page SHALL omit the bank account list section without displaying an error.
6. IF neither bank accounts nor a QRIS image are configured, THEN THE Payment_Page SHALL still display the general payment instruction note from Payment_Settings without error.

### Requirement 6: Payment Proof Upload

**User Story:** As a customer, I want to upload my payment proof for a specific invoice, so that the admin can verify my payment.

#### Acceptance Criteria

1. WHEN an invoice is selected, THE Payment_Page SHALL display a payment proof upload form containing: payment method selector, file input, notes textarea, and submit button.
2. THE Payment_Page SHALL offer payment method options with values: "transfer_bank" (displayed as "Transfer Bank"), "qris" (displayed as "QRIS"), and "cash" (displayed as "Cash").
3. THE Payment_Page SHALL accept image files in JPG, JPEG, PNG, or WEBP format with a maximum file size of 5 MB, and the notes field SHALL accept a maximum of 1000 characters.
4. THE Payment_Page SHALL submit the upload form via POST request with multipart/form-data encoding including CSRF token, customer_code, invoice_id, payment_method, payment_proof file, and notes.
5. WHEN the selected invoice has a payment_review_status of "submitted", THE Payment_Page SHALL display a warning that a previous proof is awaiting review, label the submit button "Ganti Bukti Pembayaran", and allow re-upload which replaces the previously stored file.
6. WHEN the selected invoice has a previously uploaded proof, THE Payment_Page SHALL provide a link to view the existing proof in a new tab.
7. WHEN the upload is successful, THE Payment_Page SHALL redirect back to the payment page for the same customer_code and invoice_id with a success flash message.
8. IF the upload fails validation, THEN THE Payment_Page SHALL display the first validation error message in an error alert.
9. IF the submitted customer_code does not match any existing customer, THEN THE Payment_Page SHALL redirect back with an error message indicating the customer was not found.
10. IF the selected invoice has a status of "paid" or "cancelled", THEN THE Payment_Page SHALL redirect back with an error message indicating the invoice cannot accept payment proof.

### Requirement 7: Responsive Design

**User Story:** As a customer using a mobile phone, I want the payment page to be fully usable on small screens, so that I can pay my bills from any device.

#### Acceptance Criteria

1. THE Payment_Page SHALL render all content and interactive elements without requiring horizontal scrolling on viewport widths from 320px to 1920px.
2. WHILE the viewport is below 768px, THE Payment_Page SHALL stack all grid columns into a single column layout.
3. WHILE the viewport is below 768px, THE Payment_Page SHALL render the search input and submit button at 100% of the container width.
4. THE Payment_Page SHALL use Tabler's built-in responsive grid classes (`row`, `col-md-*`, `col-lg-*`) for layout responsiveness.
5. WHILE the viewport is below 768px, THE Payment_Page SHALL render all clickable elements (buttons, links, selection controls) with a minimum touch-target size of 44×44 CSS pixels.
6. THE Payment_Page SHALL display all text content at a minimum computed font size of 14px at every supported viewport width.

### Requirement 8: Informational Content Preservation

**User Story:** As a customer, I want to see clear payment instructions and FAQ, so that I understand the payment process without needing to contact support.

#### Acceptance Criteria

1. THE Payment_Page SHALL display a "Tata Cara Bayar" (payment guide) section containing exactly 4 numbered steps displayed in sequential order from 1 to 4, where each step includes a numeric indicator, a title, and a description.
2. THE Payment_Page SHALL display a "Pertanyaan Umum" (FAQ) section containing exactly 3 items, where each item displays both the question text and the answer text without requiring user interaction to reveal the answer.
3. THE Payment_Page SHALL preserve the informational meaning of the existing 4 payment steps (enter customer ID, select invoice, pay the exact amount, upload proof of transfer) and 3 FAQ items (customer ID not found, payment not yet confirmed, paying multiple invoices) such that step titles and FAQ questions remain recognizably equivalent in topic and guidance.
4. THE Payment_Page SHALL display both the payment guide section and the FAQ section without requiring user authentication or customer ID lookup.
