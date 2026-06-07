# Requirements Document

## Introduction

This feature adds historical traffic accounting to the Netking ISP admin panel. Currently, the system supports real-time traffic monitoring via MikroTik API (`monitorTraffic`/`monitorAllInterfaces`) and can read active PPPoE session byte counters, but no historical data is stored. This feature introduces periodic traffic collection (every 5 minutes), daily and monthly aggregation, per-customer usage graphs, top consumer dashboards, optional quota alerts, and CSV export — enabling admins to track bandwidth usage over time per customer.

## Glossary

- **Traffic_Collector**: The scheduled Artisan command that polls MikroTik routers every 5 minutes to read PPPoE session byte counters and calculates traffic deltas
- **Traffic_Aggregator**: The scheduled Artisan command that rolls up raw 5-minute readings into daily summaries and daily summaries into monthly summaries
- **Traffic_Dashboard**: The admin panel UI components that display traffic usage graphs and top consumer widgets
- **Customer**: A subscriber with a PPPoE account managed by Netking, identified by `pppoe_user` and belonging to an Area
- **Area**: A network zone with its own MikroTik router, defined by `router_ip`, `router_user`, and `router_pass`
- **Delta_Bytes**: The difference in byte counters between two consecutive readings for a PPPoE session, representing actual traffic consumed in that interval
- **Counter_Reset**: The event when a PPPoE session reconnects and the byte counter returns to zero, requiring special handling to avoid negative delta values
- **Quota_Threshold**: A configurable monthly bandwidth limit (in GB) per customer that triggers an alert when exceeded
- **Data_Retention_Policy**: The rule that daily traffic records are kept for 90 days and monthly records are kept indefinitely

## Requirements

### Requirement 1: Periodic Traffic Data Collection

**User Story:** As an ISP admin, I want the system to automatically collect traffic byte counters from all active PPPoE sessions every 5 minutes, so that bandwidth usage data is captured for historical analysis.

#### Acceptance Criteria

1. THE Traffic_Collector SHALL execute as a scheduled Artisan command every 5 minutes via the Laravel scheduler
2. WHEN the Traffic_Collector executes, THE Traffic_Collector SHALL query `/ppp/active/print` on each Area router to retrieve `bytes-in` and `bytes-out` for all active PPPoE sessions
3. WHEN the Traffic_Collector retrieves byte counters, THE Traffic_Collector SHALL calculate Delta_Bytes by subtracting the previous reading (stored in cache or database) from the current reading
4. WHEN the Traffic_Collector processes multiple Areas, THE Traffic_Collector SHALL connect to each Area router sequentially using `MikroTikService::forArea()`
5. THE Traffic_Collector SHALL store the current byte counters as the baseline for the next collection cycle
6. IF a router connection fails during collection, THEN THE Traffic_Collector SHALL log the error and continue processing remaining Areas without interruption
7. THE Traffic_Collector SHALL use `withoutOverlapping()` to prevent concurrent execution of multiple collection cycles

### Requirement 2: Counter Reset Handling

**User Story:** As an ISP admin, I want the system to correctly handle PPPoE session reconnects where byte counters reset to zero, so that traffic data remains accurate.

#### Acceptance Criteria

1. WHEN the current byte counter is less than the previous reading for a session, THE Traffic_Collector SHALL treat the current counter value as the Delta_Bytes for that interval (assuming a Counter_Reset occurred)
2. WHEN a PPPoE session disappears from the active session list, THE Traffic_Collector SHALL remove the stored baseline for that session
3. WHEN a PPPoE session appears that has no stored baseline, THE Traffic_Collector SHALL store the current counters as the baseline and record zero Delta_Bytes for that interval

### Requirement 3: Daily Traffic Aggregation

**User Story:** As an ISP admin, I want traffic data summed per customer per day, so that I can view daily bandwidth consumption without querying raw interval data.

#### Acceptance Criteria

1. THE Traffic_Aggregator SHALL accumulate Delta_Bytes into the `traffic_daily` table, grouped by `customer_id` and `date`
2. THE `traffic_daily` table SHALL store `customer_id`, `date`, `bytes_in`, `bytes_out`, and `area_id` columns
3. THE `traffic_daily` table SHALL use `BIGINT UNSIGNED` data type for `bytes_in` and `bytes_out` columns to handle large cumulative values
4. WHEN the Traffic_Collector computes a valid Delta_Bytes, THE Traffic_Collector SHALL increment the corresponding `traffic_daily` row for the current date (upsert pattern)
5. THE `traffic_daily` table SHALL enforce a unique constraint on (`customer_id`, `date`) to prevent duplicate daily records

### Requirement 4: Monthly Traffic Rollup

**User Story:** As an ISP admin, I want monthly traffic summaries per customer, so that I can quickly review billing-period usage without scanning daily records.

#### Acceptance Criteria

1. THE Traffic_Aggregator SHALL compute monthly summaries by summing `bytes_in` and `bytes_out` from `traffic_daily` grouped by `customer_id` and month
2. THE `traffic_monthly` table SHALL store `customer_id`, `month` (date representing first day of month), `total_bytes_in`, `total_bytes_out`, and `area_id` columns
3. THE `traffic_monthly` table SHALL use `BIGINT UNSIGNED` data type for `total_bytes_in` and `total_bytes_out` columns
4. THE Traffic_Aggregator SHALL execute the monthly rollup once daily (after midnight) to update the current month totals
5. THE `traffic_monthly` table SHALL enforce a unique constraint on (`customer_id`, `month`) to prevent duplicate monthly records

### Requirement 5: Customer Traffic Usage Graph

**User Story:** As an ISP admin, I want to see a line or bar chart showing daily traffic for a specific customer over the last 30 days, so that I can visually assess bandwidth usage patterns.

#### Acceptance Criteria

1. THE Traffic_Dashboard SHALL display a daily traffic chart on the customer detail page showing the last 30 days of usage
2. THE Traffic_Dashboard SHALL render the chart using Chart.js or ApexCharts (libraries already available in the admin panel)
3. THE Traffic_Dashboard SHALL display separate data series for download (`bytes_in`) and upload (`bytes_out`) traffic
4. THE Traffic_Dashboard SHALL format byte values in human-readable units (KB, MB, GB) on the chart axes
5. WHEN a customer has fewer than 30 days of traffic data, THE Traffic_Dashboard SHALL display only the available data without showing empty placeholder days

### Requirement 6: Top Bandwidth Consumers Widget

**User Story:** As an ISP admin, I want a dashboard widget showing the top 10 bandwidth consumers for the current month, so that I can quickly identify heavy users.

#### Acceptance Criteria

1. THE Traffic_Dashboard SHALL display a "Top 10 Consumers" widget on the admin dashboard page
2. THE Traffic_Dashboard SHALL rank customers by total bytes (bytes_in + bytes_out) for the current calendar month
3. THE Traffic_Dashboard SHALL display customer name, area name, and total usage in human-readable format (GB) for each entry
4. WHEN there are fewer than 10 customers with traffic data, THE Traffic_Dashboard SHALL display only the available entries
5. THE Traffic_Dashboard SHALL allow filtering the top consumers widget by Area

### Requirement 7: Usage Quota Alerts

**User Story:** As an ISP admin, I want to configure optional monthly bandwidth quotas per customer and receive alerts when thresholds are exceeded, so that I can implement fair usage policies in the future.

#### Acceptance Criteria

1. WHERE the quota feature is enabled, THE System SHALL allow admins to set a Quota_Threshold (in GB) per customer or per package
2. WHERE a Quota_Threshold is configured, THE Traffic_Aggregator SHALL compare the current month total bytes against the threshold after each daily rollup
3. WHEN a customer's monthly usage exceeds the configured Quota_Threshold, THE System SHALL create an admin notification indicating the customer has exceeded the quota
4. THE System SHALL send the quota alert only once per customer per billing month (not repeatedly after each aggregation)
5. WHERE no Quota_Threshold is configured for a customer or package, THE System SHALL skip quota checking for that customer

### Requirement 8: Traffic Data CSV Export

**User Story:** As an ISP admin, I want to export traffic data as CSV, so that I can generate custom reports or import data into external tools.

#### Acceptance Criteria

1. THE Traffic_Dashboard SHALL provide a CSV export function for daily traffic data
2. THE Traffic_Dashboard SHALL allow filtering the export by date range, customer, and Area
3. THE CSV export SHALL include columns: customer_name, customer_code, pppoe_user, area_name, date, bytes_in, bytes_out, total_bytes
4. THE CSV export SHALL format byte values as raw integers (not human-readable) to preserve precision for external processing
5. WHEN the export result exceeds 10,000 rows, THE Traffic_Dashboard SHALL generate the export as a background job and notify the admin when complete

### Requirement 9: Data Retention Policy

**User Story:** As an ISP admin, I want old traffic data automatically pruned according to retention rules, so that database storage remains manageable over time.

#### Acceptance Criteria

1. THE Traffic_Aggregator SHALL delete `traffic_daily` records older than 90 days during the daily aggregation run
2. THE Traffic_Aggregator SHALL retain `traffic_monthly` records indefinitely (no automatic deletion)
3. WHEN the retention cleanup executes, THE Traffic_Aggregator SHALL log the number of deleted records
4. THE Traffic_Aggregator SHALL execute the retention cleanup after the monthly rollup completes to ensure data is aggregated before deletion
