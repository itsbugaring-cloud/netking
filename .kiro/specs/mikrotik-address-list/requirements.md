# Requirements Document

## Introduction

This feature adds MikroTik address-list management to the Netking ISP admin panel, enabling a more flexible customer isolation (isolir) mechanism. Instead of disabling PPPoE secrets entirely (which cuts off the customer), ISPs can add customer IPs to a firewall address-list (e.g., "isolir") that redirects them to a payment page via MikroTik NAT rules. This preserves connectivity while incentivizing payment.

The feature supports manual single-customer isolation, bulk isolation of overdue customers, automatic scheduled isolation, and status synchronization between the router and the Netking database.

## Glossary

- **Address_List_Service**: The service component in Netking that communicates with the MikroTik RouterOS API to manage firewall address-list entries.
- **Isolir_List**: The configurable MikroTik firewall address-list name used for customer isolation (default: "isolir").
- **Customer_Record**: The database representation of a customer in the Netking system, including their PPPoE credentials, IP address, area, and isolation status.
- **Scheduler**: The Laravel console kernel scheduling system that triggers automated commands at configured intervals.
- **Admin_Panel**: The Netking web-based administration interface used by ISP operators.
- **Router**: The MikroTik RouterOS device associated with an area, accessed via the RouterOS API.
- **Timeout**: A RouterOS address-list parameter that automatically removes the entry after a specified duration (e.g., "7d" for 7 days).
- **Bulk_Operation**: A batch process that applies address-list actions to multiple customers in a single operation with rate-limiting controls.

## Requirements

### Requirement 1: View Address List Entries

**User Story:** As an ISP admin, I want to view all entries in the isolir address-list from the router, so that I can see which customers are currently isolated.

#### Acceptance Criteria

1. WHEN the admin navigates to the address-list view, THE Address_List_Service SHALL retrieve all entries from the configured Isolir_List on the Router using the `/ip/firewall/address-list/print` endpoint.
2. THE Admin_Panel SHALL display each address-list entry with the following fields: IP address, list name, timeout remaining, comment, and creation time.
3. WHEN entries are retrieved, THE Admin_Panel SHALL indicate which entries correspond to known customers in the Customer_Record by matching the IP address.
4. IF the Router connection fails, THEN THE Admin_Panel SHALL display an error message with the connection failure reason and the Router host identifier.
5. WHERE filtering is enabled, THE Admin_Panel SHALL allow filtering entries by list name, IP address, or customer name.

### Requirement 2: Add Customer to Address List

**User Story:** As an ISP admin, I want to add a customer's IP to the isolir address-list, so that I can isolate them and redirect their traffic to a payment page.

#### Acceptance Criteria

1. WHEN the admin initiates isolation for a customer, THE Address_List_Service SHALL add the customer's remote IP address to the configured Isolir_List on the Router using the `/ip/firewall/address-list/add` endpoint.
2. WHEN adding an address-list entry, THE Address_List_Service SHALL include a comment containing the customer name and customer code for identification.
3. WHERE a timeout value is specified, THE Address_List_Service SHALL include the timeout parameter (e.g., "7d") in the add request so that the entry auto-removes after the specified duration.
4. WHEN the address-list entry is successfully added on the Router, THE Address_List_Service SHALL update the Customer_Record to set the isolation status flag to true and record the isolation timestamp.
5. IF the customer's remote IP address is empty or null, THEN THE Address_List_Service SHALL reject the isolation request and return a descriptive error indicating the missing IP.
6. IF the customer is already present in the Isolir_List, THEN THE Address_List_Service SHALL return a notice indicating the customer is already isolated without creating a duplicate entry.

### Requirement 3: Remove Customer from Address List

**User Story:** As an ISP admin, I want to remove a customer from the isolir address-list, so that their internet access is restored to normal after payment.

#### Acceptance Criteria

1. WHEN the admin initiates de-isolation for a customer, THE Address_List_Service SHALL find the customer's entry in the Isolir_List using the `/ip/firewall/address-list/find` endpoint and remove it using the `/ip/firewall/address-list/remove` endpoint.
2. WHEN the address-list entry is successfully removed from the Router, THE Address_List_Service SHALL update the Customer_Record to set the isolation status flag to false and record the de-isolation timestamp.
3. IF the customer's IP is not found in the Isolir_List, THEN THE Address_List_Service SHALL update the Customer_Record isolation status to false and return a notice indicating the entry was not found on the Router.
4. IF the Router connection fails during removal, THEN THE Address_List_Service SHALL retain the current isolation status in the Customer_Record and return a descriptive error.

### Requirement 4: Bulk Isolir for Overdue Customers

**User Story:** As an ISP admin, I want to isolate multiple overdue customers at once, so that I can efficiently enforce payment for all delinquent accounts.

#### Acceptance Criteria

1. WHEN the admin initiates bulk isolation, THE Address_List_Service SHALL identify all active customers with unpaid invoices past the configured grace period.
2. THE Bulk_Operation SHALL process customers sequentially with a configurable delay between each Router API call (default: 500ms) to avoid Router rate limiting.
3. WHEN processing each customer in the bulk operation, THE Address_List_Service SHALL apply the same add-to-address-list logic as single customer isolation (Requirement 2).
4. THE Bulk_Operation SHALL track and report the result for each customer: success, skipped (already isolated), or failed (with error reason).
5. IF a single customer operation fails during bulk processing, THEN THE Bulk_Operation SHALL log the error, continue processing remaining customers, and include the failure in the summary report.
6. THE Admin_Panel SHALL display a progress indicator and final summary showing total processed, successful, skipped, and failed counts.

### Requirement 5: Automatic Scheduled Isolation

**User Story:** As an ISP admin, I want overdue customers to be automatically isolated on a daily schedule, so that isolation enforcement happens without manual intervention.

#### Acceptance Criteria

1. THE Scheduler SHALL execute the auto-isolation command daily at a configurable time (default: 08:00).
2. WHEN the scheduled command runs, THE Address_List_Service SHALL apply the same bulk isolation logic (Requirement 4) to all customers matching the overdue criteria.
3. THE Scheduler SHALL prevent overlapping runs of the auto-isolation command using a mutex lock.
4. THE Scheduler SHALL log all actions to a dedicated log file including start time, customers processed, results per customer, and completion time.
5. WHERE a dry-run option is enabled, THE Scheduler SHALL simulate the isolation process and report which customers would be isolated without making Router API calls or updating Customer_Records.
6. THE Scheduler SHALL use the same configurable grace period (in days) as the bulk operation to determine which invoices are considered overdue.

### Requirement 6: Isolation Status Indicator

**User Story:** As an ISP admin, I want to see which customers are currently isolated directly in the customer list, so that I can quickly identify their status without navigating to the router.

#### Acceptance Criteria

1. THE Admin_Panel SHALL display the isolation status as a visible indicator (badge or icon) in the customer list view for each Customer_Record where the isolation flag is true.
2. WHEN the admin views a customer detail page, THE Admin_Panel SHALL show the isolation status, isolation date, and expected timeout expiry (if a timeout was set).
3. THE Admin_Panel SHALL allow filtering the customer list by isolation status (isolated, not isolated, all).
4. WHEN the Address_List_Service modifies a customer's isolation state (add or remove), THE Customer_Record SHALL be updated synchronously so the indicator reflects the current state.

### Requirement 7: Address List Configuration

**User Story:** As an ISP admin, I want to configure the address-list name and default timeout, so that I can adapt the feature to my network's specific setup.

#### Acceptance Criteria

1. THE Admin_Panel SHALL provide a settings section where the admin can configure: the address-list name (default: "isolir"), default timeout value (default: empty/no timeout), and the inter-request delay for bulk operations (default: 500ms).
2. THE Address_List_Service SHALL read the configured address-list name from the settings and use it for all add, remove, and list operations.
3. IF the address-list name setting is empty, THEN THE Address_List_Service SHALL use "isolir" as the default list name.
4. THE Admin_Panel SHALL validate that the timeout value follows the RouterOS duration format (e.g., "1d", "12h", "7d", "30m") before saving.

### Requirement 8: Isolation Status Synchronization

**User Story:** As an ISP admin, I want the isolation status in Netking to stay in sync with the actual router state, so that the displayed status accurately reflects reality.

#### Acceptance Criteria

1. WHEN the admin triggers a manual sync, THE Address_List_Service SHALL retrieve all entries from the Isolir_List and compare them with Customer_Records to identify discrepancies.
2. WHEN a customer's IP is found in the Isolir_List but the Customer_Record shows not isolated, THE Address_List_Service SHALL update the Customer_Record isolation flag to true.
3. WHEN a customer's IP is not found in the Isolir_List but the Customer_Record shows isolated, THE Address_List_Service SHALL update the Customer_Record isolation flag to false (entry may have expired via timeout).
4. THE Address_List_Service SHALL log all synchronization corrections with the customer identifier and the direction of the correction.

### Requirement 9: Router-Side NAT Redirect Documentation

**User Story:** As an ISP admin, I want documentation on the required MikroTik NAT rule setup, so that I can configure my router to redirect isolated customers to a payment page.

#### Acceptance Criteria

1. THE Admin_Panel SHALL provide a documentation section explaining the required MikroTik NAT dst-nat rule that redirects traffic from the Isolir_List to the payment page server.
2. THE Admin_Panel SHALL display an example RouterOS command for the NAT redirect rule with configurable placeholders for the payment page IP and port.
3. THE Admin_Panel SHALL explain that the NAT redirect rule is configured on the Router manually and is not managed by the Netking system.
