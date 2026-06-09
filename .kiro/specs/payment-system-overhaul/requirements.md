# Requirements Document

## Introduction

Complete overhaul of the Netking ISP billing payment system. The existing invoice-based system (auto-generate scheduler, auto-approve on proof upload) is replaced with a simpler payment recording system. Customers or PICs upload proof on a public page, admin reviews and approves/rejects, or admin marks payment manually. The `invoices` table is fully removed and replaced by a new `payments` table. All "Partner/Mitra" labels are renamed to "PIC". An Excel export of customer payment data is provided.

## Glossary

- **Payment_System**: The new payment recording and approval system replacing the old invoice system
- **Payment_Page**: The public-facing page at `/bayar` where customers or PICs upload payment proof
- **Admin_Panel**: The authenticated admin interface for managing payments, customers, and settings
- **Review_Page**: The admin page showing pending payments queue for approval/rejection
- **PIC**: Person In Charge — a user who manages a group of customers (previously called Partner/Mitra)
- **Customer**: An ISP subscriber identified by a unique customer_code (format: NK######)
- **Payment**: A record in the `payments` table representing a single payment transaction
- **Scheduler**: The Laravel console kernel that runs periodic commands
- **Manual_Payment**: A payment recorded by admin without proof upload (e.g., verified via bank mutation)

## Requirements

### Requirement 1

**User Story:** As an admin, I want the invoice auto-generation scheduler disabled and auto-approve removed, so that the system no longer creates invoices or auto-confirms payments.

#### Acceptance Criteria

1.1. WHEN the scheduler runs, THE Payment_System SHALL NOT execute the `invoices:generate` command

1.2. WHEN a customer uploads payment proof on the Payment_Page, THE Payment_System SHALL store the payment as pending status without auto-approving

1.3. THE Payment_System SHALL remove all references to the `invoices` table and the `Invoice` model from the active codebase

### Requirement 2

**User Story:** As a customer or PIC, I want to upload payment proof on the public payment page, so that the admin can verify and approve my payment.

#### Acceptance Criteria

2.1. WHEN a customer or PIC visits `/bayar?customer_code=X`, THE Payment_Page SHALL display the customer information, package details, and monthly amount

2.2. WHEN a customer or PIC submits payment proof, THE Payment_System SHALL create a payment record with status `pending`, the uploaded proof file, and periode set to the current month and year

2.3. WHEN a payment proof is submitted successfully, THE Payment_Page SHALL display the message "Pembayaran sedang diproses" without showing any pending status details

2.4. WHEN a customer_code is not found, THE Payment_Page SHALL display an error message "ID pelanggan tidak ditemukan"

2.5. WHEN a payment proof file exceeds 5MB or is not an image (jpg, jpeg, png, webp), THE Payment_Page SHALL reject the upload with a validation error message

2.6. THE Payment_Page SHALL display available payment accounts (BRI, BNI, Mandiri, BCA) and QRIS information from system settings

### Requirement 3

**User Story:** As an admin or finance officer, I want a review page to see pending payment proofs and approve or reject them, so that I can verify payments before confirming.

#### Acceptance Criteria

3.1. WHEN an admin visits the Review_Page, THE Admin_Panel SHALL display all payments with status `pending` ordered by creation date ascending

3.2. WHEN an admin views a pending payment, THE Review_Page SHALL display the payment proof image, customer name, period, amount, payment method, and destination account

3.3. WHEN an admin approves a payment, THE Payment_System SHALL update the payment status to `approved`, record the approving user and timestamp

3.4. WHEN an admin rejects a payment, THE Payment_System SHALL update the payment status to `rejected` and record the rejection reason

3.5. IF an admin attempts to approve or reject a payment that is not in `pending` status, THEN THE Payment_System SHALL return an error and prevent the state change

### Requirement 4

**User Story:** As an admin, I want to mark a payment manually without proof upload, so that I can record payments I verified through bank mutations or cash receipts.

#### Acceptance Criteria

4.1. WHEN an admin initiates "Tandai Bayar Manual" from a customer detail page, THE Admin_Panel SHALL present a form to select period (month/year), payment method, destination account, and optional notes

4.2. WHEN an admin submits a manual payment, THE Payment_System SHALL create a payment record with status `approved`, the admin as `approved_by_user_id`, and the current timestamp as `approved_at`

4.3. THE Payment_System SHALL allow manual payments with method `transfer` or `cash`

4.4. WHEN a manual payment is created, THE Payment_System SHALL record `created_by_user_id` as the authenticated admin

### Requirement 5

**User Story:** As a developer, I want a new `payments` table with proper schema, so that payment data is stored correctly and the old invoices table can be removed.

#### Acceptance Criteria

5.1. THE Payment_System SHALL create a `payments` table with columns: id, customer_id (FK), periode_bulan (tinyint), periode_tahun (smallint), jumlah (decimal 12,2), metode (enum: transfer, cash), rekening_tujuan (string), bukti_path (nullable string), bukti_original_name (nullable string), status (enum: pending, approved, rejected), approved_by_user_id (FK users, nullable), approved_at (nullable datetime), reject_reason (nullable string), catatan (nullable text), created_by_user_id (FK users, nullable), timestamps

5.2. THE Payment_System SHALL create a Payment model with relationships to Customer, ApprovedBy (User), and CreatedBy (User)

5.3. THE Payment_System SHALL include a migration to drop the `invoices` table

### Requirement 6

**User Story:** As an admin, I want all "Partner/Mitra" labels renamed to "PIC" across the admin interface, so that terminology is consistent with our organization.

#### Acceptance Criteria

6.1. WHEN displaying customer information in the Admin_Panel, THE Admin_Panel SHALL use the label "PIC" instead of "Partner" or "Mitra"

6.2. THE Admin_Panel SHALL rename the sidebar navigation label from "Partner" or "Mitra" to "PIC" where applicable

### Requirement 7

**User Story:** As an admin, I want to export customer payment data to Excel, so that I can review billing status offline and share reports.

#### Acceptance Criteria

7.1. WHEN an admin clicks the export button on the customer list page, THE Payment_System SHALL generate an Excel file with columns: No, PIC, Area, Nama, No. HP, Layanan, Bayar (Rp), Status, Tgl Berlangganan, Tgl Bayar, Pembayaran, Rekening, Approved by, Keterangan

7.2. THE Payment_System SHALL support filtering the export by area (all areas or specific area)

7.3. WHEN generating the export, THE Payment_System SHALL use billing_start_date as "Tgl Berlangganan"

7.4. WHEN generating the export, THE Payment_System SHALL use the latest approved payment date as "Tgl Bayar"

7.5. THE Payment_System SHALL use the maatwebsite/excel package for Excel generation

### Requirement 8

**User Story:** As an admin, I want the payment period to default to the current month but be editable during review, so that corrections can be made when needed.

#### Acceptance Criteria

8.1. WHEN a customer submits payment proof, THE Payment_System SHALL set periode_bulan and periode_tahun to the current month and year

8.2. WHEN an admin reviews a pending payment, THE Admin_Panel SHALL allow the admin to change the periode_bulan and periode_tahun before approving

8.3. WHEN an admin creates a manual payment, THE Admin_Panel SHALL allow selection of any month and year for the payment period
