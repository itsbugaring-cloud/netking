# Requirements Document

## Introduction

The MikroTik System Dashboard provides ISP administrators with a centralized view of all area routers' system health and resource utilization. Currently, Netking has no system information dashboard — admins must SSH or Winbox into each router individually to check CPU, memory, disk, and uptime. This feature surfaces that data directly within the admin panel using the existing MikroTikService and per-area router credentials, presented in a multi-router card grid with health alerts and auto-refresh capabilities.

## Glossary

- **Dashboard**: The admin panel page displaying system resource cards for all area routers
- **Router_Card**: A UI card component showing a single router's system resource data and health status
- **Health_Indicator**: A visual badge or color coding reflecting the operational health of a router (online/offline, warning, critical)
- **MikroTikService**: The existing Laravel service class that connects to MikroTik routers via the RouterOS API
- **Area_Router**: A MikroTik router associated with a specific service area, configured in the areas table with router_ip, router_user, and router_pass
- **Resource_Data**: The system resource information retrieved from a router including CPU load, memory usage, disk usage, uptime, architecture, version, and board name
- **Connection_Log**: A database record tracking router online/offline status transitions over time
- **Refresh_Interval**: The configurable time period between automatic dashboard data updates, defaulting to 30 seconds

## Requirements

### Requirement 1: System Resource Data Retrieval

**User Story:** As an ISP admin, I want to retrieve system resource data from each area router, so that I can monitor CPU, memory, disk, uptime, and system information from the panel.

#### Acceptance Criteria

1. WHEN the Dashboard page is loaded, THE MikroTikService SHALL query `/system/resource/print` for each Area_Router and return CPU load percentage, total memory, free memory, total HDD space, free HDD space, uptime, architecture name, RouterOS version, and board name
2. WHEN the Dashboard page is loaded, THE MikroTikService SHALL query `/system/identity/print` for each Area_Router and return the router identity name
3. WHEN the Dashboard page is loaded, THE MikroTikService SHALL query `/system/license/print` for each Area_Router and return the license level and software-id
4. IF an Area_Router is unreachable or the API connection times out within 5 seconds, THEN THE Dashboard SHALL mark that router as offline and display the last known data with a timestamp

### Requirement 2: Multi-Router Dashboard Layout

**User Story:** As an ISP admin, I want to see all area routers in a grid layout, so that I can quickly scan the health of my entire network from one page.

#### Acceptance Criteria

1. THE Dashboard SHALL display one Router_Card per Area_Router in a responsive grid layout using the Tabler UI card component
2. WHEN all router data is loaded, THE Dashboard SHALL sort Router_Cards by area name alphabetically
3. THE Router_Card SHALL display the router identity name, area name, RouterOS version, board name, uptime, CPU load percentage, memory usage percentage, and disk usage percentage
4. THE Router_Card SHALL display a Health_Indicator badge showing one of: "Online" (green), "Warning" (yellow), "Critical" (red), or "Offline" (gray)

### Requirement 3: Health Alert Thresholds

**User Story:** As an ISP admin, I want routers with high resource usage to be visually highlighted, so that I can quickly identify routers that need attention.

#### Acceptance Criteria

1. WHEN a router's CPU load exceeds 80%, THE Dashboard SHALL display a "Critical" Health_Indicator on that Router_Card
2. WHEN a router's free memory is below 20% of total memory, THE Dashboard SHALL display a "Critical" Health_Indicator on that Router_Card
3. WHEN a router's free disk space is below 20% of total disk space, THE Dashboard SHALL display a "Warning" Health_Indicator on that Router_Card
4. WHEN a router's CPU load is between 60% and 80%, THE Dashboard SHALL display a "Warning" Health_Indicator on that Router_Card
5. WHEN multiple alert conditions apply to a single router, THE Dashboard SHALL display the highest severity Health_Indicator (Critical takes precedence over Warning)
6. WHEN a router is unreachable, THE Dashboard SHALL display an "Offline" Health_Indicator regardless of last known resource values

### Requirement 4: Auto-Refresh Mechanism

**User Story:** As an ISP admin, I want the dashboard to refresh automatically, so that I always see current router status without manually reloading the page.

#### Acceptance Criteria

1. THE Dashboard SHALL automatically poll for updated Resource_Data at a default Refresh_Interval of 30 seconds using AJAX requests
2. WHEN the admin changes the Refresh_Interval via the dashboard settings control, THE Dashboard SHALL use the new interval for subsequent polls
3. THE Dashboard SHALL provide Refresh_Interval options of 10 seconds, 30 seconds, 60 seconds, and 120 seconds
4. WHILE the Dashboard is polling, THE Dashboard SHALL display a subtle loading indicator without replacing the existing data
5. WHEN the AJAX poll request fails, THE Dashboard SHALL retry once after 5 seconds and display a connection warning notification if the retry also fails
6. WHEN the browser tab is not visible, THE Dashboard SHALL pause auto-refresh polling and resume when the tab becomes visible again

### Requirement 5: Router Identity and License Display

**User Story:** As an ISP admin, I want to see each router's identity, license level, and software-id, so that I can track licensing compliance and identify routers by name.

#### Acceptance Criteria

1. THE Router_Card SHALL display the router identity name as the card title
2. THE Router_Card SHALL display the license level (e.g., "Level 4", "Level 5", "Level 6") in a metadata section
3. THE Router_Card SHALL display the software-id in a metadata section
4. IF the license endpoint returns empty or the router does not support `/system/license/print`, THEN THE Router_Card SHALL display "N/A" for license level and software-id

### Requirement 6: Connection Status History

**User Story:** As an ISP admin, I want to see when routers went offline or came back online, so that I can track uptime patterns and investigate outages.

#### Acceptance Criteria

1. WHEN a router transitions from online to offline, THE Dashboard SHALL create a Connection_Log record with the router area_id, status "offline", and current timestamp
2. WHEN a router transitions from offline to online, THE Dashboard SHALL create a Connection_Log record with the router area_id, status "online", and current timestamp
3. THE Dashboard SHALL display the last 5 Connection_Log entries per router in a compact timeline on the Router_Card
4. THE Connection_Log records SHALL be retained for 90 days, after which a scheduled cleanup removes older entries
5. WHEN the Dashboard first initializes for a router with no existing Connection_Log, THE Dashboard SHALL create an initial log entry with the current detected status

### Requirement 7: Dashboard API Endpoint

**User Story:** As a frontend developer, I want a JSON API endpoint that returns all router system data, so that the AJAX polling can retrieve fresh data without reloading the page.

#### Acceptance Criteria

1. THE Dashboard SHALL expose a GET endpoint at `/admin/system-dashboard/data` that returns JSON containing Resource_Data, identity, license, and health status for all Area_Routers
2. THE endpoint SHALL query all Area_Routers concurrently using parallel processing to minimize total response time
3. WHEN an individual router query fails, THE endpoint SHALL return partial results with an error status for that specific router without failing the entire request
4. THE endpoint SHALL require admin authentication via the existing AdminMiddleware
5. THE endpoint SHALL complete the response within 10 seconds by enforcing a per-router connection timeout of 5 seconds

### Requirement 8: Optional Hardware Health Data

**User Story:** As an ISP admin, I want to see hardware health metrics like temperature and voltage when available, so that I can detect physical hardware issues.

#### Acceptance Criteria

1. WHEN a router supports `/system/health/print`, THE MikroTikService SHALL retrieve temperature and voltage readings
2. WHEN hardware health data is available, THE Router_Card SHALL display CPU temperature and board voltage in a secondary metrics section
3. IF the router does not support `/system/health/print` or returns empty data, THEN THE Router_Card SHALL omit the hardware health section without displaying errors
4. WHEN CPU temperature exceeds 70°C, THE Dashboard SHALL include a "Warning" indicator for that router's temperature metric
