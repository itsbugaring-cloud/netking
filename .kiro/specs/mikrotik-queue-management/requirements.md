# Requirements Document

## Introduction

MikroTik Simple Queue Management for the Netking ISP admin panel. This feature enables administrators to manage per-customer bandwidth queues directly from the panel, providing granular speed control beyond PPPoE profile defaults. It covers CRUD operations on simple queues, automatic queue provisioning on customer creation, queue synchronization between MikroTik routers and the customer database, temporary speed overrides with auto-revert, and real-time queue traffic monitoring.

## Glossary

- **Queue_Manager**: The Netking subsystem responsible for creating, reading, updating, and deleting simple queues on MikroTik routers via the RouterOS API.
- **Simple_Queue**: A MikroTik RouterOS queue entry that applies bandwidth limits to a specific target IP address, with parameters including max-limit, burst-limit, burst-threshold, and burst-time.
- **Queue_Sync_Engine**: The Netking subsystem that reconciles simple queues on MikroTik routers with customer records in the database, detecting orphaned and missing queues.
- **Speed_Override_Scheduler**: The Netking subsystem responsible for applying temporary speed limits to customer queues and automatically reverting them after a specified duration.
- **Queue_Monitor**: The Netking subsystem that retrieves real-time traffic rate data from MikroTik simple queues for display in the admin panel.
- **Admin**: An authenticated user with administrative privileges in the Netking panel.
- **Area_Router**: A MikroTik router associated with a specific area, identified by router_ip, router_user, and router_pass credentials on the Area model.
- **Customer**: A subscriber record in the Netking database, associated with an area, package, PPPoE username, and remote IP address.
- **Max_Limit**: The maximum bandwidth rate (upload/download) applied to a simple queue, formatted as "uploadM/downloadM" in RouterOS notation.
- **Burst_Parameters**: The combination of burst-limit, burst-threshold, and burst-time that define temporary speed bursts allowed before throttling to max-limit.
- **Queue_Name**: A deterministic identifier for a simple queue, following the convention "nk-{customer_id}" to link queues to customer records.
- **Orphaned_Queue**: A simple queue present on the router that has no corresponding active customer record in the Netking database.
- **Missing_Queue**: A customer record that should have a simple queue on the router but does not.

## Requirements

### Requirement 1: List Simple Queues

**User Story:** As an Admin, I want to view all simple queues from an area router in the admin panel, so that I can monitor bandwidth allocations across customers.

#### Acceptance Criteria

1. WHEN the Admin selects an area, THE Queue_Manager SHALL retrieve all simple queues from the corresponding Area_Router via the `/queue/simple/print` API endpoint.
2. WHEN simple queues are retrieved, THE Queue_Manager SHALL display each queue's name, target IP, max-limit, burst-limit, burst-threshold, burst-time, disabled status, and comment in a tabular format.
3. WHEN simple queues are retrieved, THE Queue_Manager SHALL match each queue to its corresponding Customer record using the Queue_Name convention and display the customer name alongside the queue.
4. IF the Area_Router is unreachable, THEN THE Queue_Manager SHALL display an error message indicating the router connection failure and the router IP address.
5. WHEN the Admin has no area selected, THE Queue_Manager SHALL prompt the Admin to select an area before displaying queues.

### Requirement 2: Create Simple Queue

**User Story:** As an Admin, I want to create a simple queue for a customer, so that I can apply specific bandwidth limits beyond the PPPoE profile default.

#### Acceptance Criteria

1. WHEN the Admin submits a queue creation form with a customer, target IP, max-limit upload, max-limit download, and optional burst parameters, THE Queue_Manager SHALL send a `/queue/simple/add` command to the Area_Router with the formatted parameters.
2. THE Queue_Manager SHALL format the queue name as "nk-{customer_id}" where customer_id is the numeric Customer ID.
3. THE Queue_Manager SHALL format max-limit as "{upload_speed}M/{download_speed}M" in RouterOS notation.
4. WHEN burst parameters are provided, THE Queue_Manager SHALL format burst-limit, burst-threshold, and burst-time in RouterOS notation and include them in the queue creation command.
5. THE Queue_Manager SHALL set the queue target to the Customer's remote_ip address appended with "/32".
6. THE Queue_Manager SHALL set the queue comment to the Customer's name and PPPoE username for identification.
7. IF queue creation succeeds, THEN THE Queue_Manager SHALL display a success notification and redirect the Admin to the queue list.
8. IF queue creation fails due to a duplicate queue name, THEN THE Queue_Manager SHALL display an error message indicating the queue already exists for the customer.
9. IF the Area_Router is unreachable, THEN THE Queue_Manager SHALL display an error message and retain the form data for retry.

### Requirement 3: Edit Simple Queue

**User Story:** As an Admin, I want to edit an existing simple queue, so that I can adjust a customer's bandwidth limits or burst configuration.

#### Acceptance Criteria

1. WHEN the Admin submits an edit form for an existing queue with updated max-limit or burst parameters, THE Queue_Manager SHALL send a `/queue/simple/set` command to the Area_Router with the queue's `.id` and updated parameters.
2. THE Queue_Manager SHALL pre-populate the edit form with the queue's current values retrieved from the router.
3. WHEN the edit is submitted, THE Queue_Manager SHALL validate that upload and download speed values are positive integers.
4. IF the queue edit succeeds, THEN THE Queue_Manager SHALL display a success notification with the updated speed values.
5. IF the queue's `.id` is not found on the router, THEN THE Queue_Manager SHALL display an error indicating the queue no longer exists and refresh the queue list.
6. IF the Area_Router is unreachable, THEN THE Queue_Manager SHALL display an error message and retain the form data for retry.

### Requirement 4: Delete Simple Queue

**User Story:** As an Admin, I want to delete a simple queue, so that I can remove bandwidth limits that are no longer needed.

#### Acceptance Criteria

1. WHEN the Admin confirms queue deletion, THE Queue_Manager SHALL send a `/queue/simple/remove` command to the Area_Router with the queue's `.id`.
2. THE Queue_Manager SHALL require explicit confirmation from the Admin before executing the delete command.
3. IF the queue deletion succeeds, THEN THE Queue_Manager SHALL display a success notification and remove the queue from the displayed list.
4. IF the queue's `.id` is not found on the router, THEN THE Queue_Manager SHALL display a warning that the queue was already removed and refresh the list.
5. IF the Area_Router is unreachable, THEN THE Queue_Manager SHALL display an error message indicating the deletion could not be completed.

### Requirement 5: Auto-Create Queue on Customer Provisioning

**User Story:** As an Admin, I want a simple queue to be automatically created when a new customer is provisioned, so that bandwidth limits matching the customer's package are applied without manual intervention.

#### Acceptance Criteria

1. WHEN a new Customer is created with an assigned Package and remote_ip, THE Queue_Manager SHALL dispatch an asynchronous job to create a simple queue on the customer's Area_Router.
2. THE Queue_Manager SHALL set the auto-created queue's max-limit to the Package's speed_up and speed_down values formatted as "{speed_up}M/{speed_down}M".
3. THE Queue_Manager SHALL set the auto-created queue's name to "nk-{customer_id}" and target to the Customer's remote_ip with "/32" suffix.
4. IF the Customer has no remote_ip assigned at creation time, THEN THE Queue_Manager SHALL skip queue creation and log a warning.
5. IF the Customer has no Package assigned at creation time, THEN THE Queue_Manager SHALL skip queue creation and log a warning.
6. IF the Area_Router is unreachable during auto-creation, THEN THE Queue_Manager SHALL retry the job up to 3 times with exponential backoff and log each failure.
7. WHEN the customer's Package is changed, THE Queue_Manager SHALL dispatch a job to update the existing queue's max-limit to match the new Package speed values.

### Requirement 6: Queue Synchronization

**User Story:** As an Admin, I want to reconcile MikroTik queues with the customer database, so that I can detect orphaned queues without customers and customers missing their queues.

#### Acceptance Criteria

1. WHEN the Admin triggers a queue sync for an area, THE Queue_Sync_Engine SHALL retrieve all simple queues from the Area_Router and all active Customer records for that area from the database.
2. THE Queue_Sync_Engine SHALL identify Orphaned_Queues by finding queues with names matching the "nk-{id}" pattern where no corresponding active Customer record exists.
3. THE Queue_Sync_Engine SHALL identify Missing_Queues by finding active Customers with a remote_ip and Package that have no corresponding queue on the router.
4. WHEN sync completes, THE Queue_Sync_Engine SHALL display a report showing the count and details of orphaned queues, missing queues, and matched queues.
5. WHEN orphaned queues are identified, THE Queue_Sync_Engine SHALL offer the Admin an option to delete selected orphaned queues from the router.
6. WHEN missing queues are identified, THE Queue_Sync_Engine SHALL offer the Admin an option to create queues for selected customers using their Package speed values.
7. IF the Area_Router is unreachable, THEN THE Queue_Sync_Engine SHALL display an error message and abort the sync operation.
8. THE Queue_Sync_Engine SHALL also detect speed mismatches where a queue exists but its max-limit does not match the Customer's current Package speed values.

### Requirement 7: Temporary Speed Override

**User Story:** As an Admin, I want to apply a temporary speed override to a customer's queue, so that I can provide speed boosts or trial upgrades that automatically revert after a set duration.

#### Acceptance Criteria

1. WHEN the Admin submits a speed override with a customer, override upload speed, override download speed, and duration in days, THE Speed_Override_Scheduler SHALL update the customer's queue max-limit to the override values on the Area_Router.
2. THE Speed_Override_Scheduler SHALL store the override record in the database with the original speed values, override speed values, start time, and expiry time.
3. THE Speed_Override_Scheduler SHALL display active overrides with remaining duration on the customer's queue entry.
4. WHEN an override's expiry time is reached, THE Speed_Override_Scheduler SHALL revert the queue's max-limit to the original Package speed values on the Area_Router.
5. THE Speed_Override_Scheduler SHALL execute expiry checks via a scheduled Laravel command that runs at a configurable interval.
6. IF the Area_Router is unreachable during revert, THEN THE Speed_Override_Scheduler SHALL retry the revert up to 3 times with exponential backoff and alert the Admin if all retries fail.
7. WHEN the Admin cancels an active override manually, THE Speed_Override_Scheduler SHALL immediately revert the queue to original speed values and mark the override as cancelled.
8. THE Speed_Override_Scheduler SHALL prevent multiple concurrent overrides on the same customer queue.

### Requirement 8: Queue Traffic Monitoring

**User Story:** As an Admin, I want to see real-time traffic rates for each simple queue, so that I can monitor actual bandwidth usage per customer.

#### Acceptance Criteria

1. WHEN the Admin views the queue list, THE Queue_Monitor SHALL display the current upload and download rate for each queue in human-readable format (Kbps/Mbps).
2. THE Queue_Monitor SHALL retrieve traffic data by reading the queue's rate fields from the `/queue/simple/print` response which includes real-time rate counters.
3. WHEN the Admin requests a traffic refresh, THE Queue_Monitor SHALL fetch updated rate data from the Area_Router and update the displayed values without full page reload.
4. THE Queue_Monitor SHALL display total bytes transferred (upload and download) for each queue since the queue's last counter reset.
5. IF the Area_Router is unreachable during monitoring, THEN THE Queue_Monitor SHALL display stale data indicators showing the last successful fetch time.
6. THE Queue_Monitor SHALL format rates using appropriate units: bps for rates below 1 Kbps, Kbps for rates below 1 Mbps, and Mbps for rates at or above 1 Mbps.
