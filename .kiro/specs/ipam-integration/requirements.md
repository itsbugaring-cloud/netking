# Requirements Document

## Introduction

Migrate the standalone Netking IP Manager application (currently running as a Docker container on Proxmox CT 100) into the main Netking.id admin panel (VM 103). This eliminates the separate IPAM service by integrating its MikroTik router scanning, OLT mapping, subnet management, WireGuard peer visibility, and audit logging capabilities directly into the existing admin panel. After successful migration, CT 100 will be decommissioned.

The IP Manager connects to MikroTik routers via RouterOS v7 REST API over WireGuard tunnels. It scans routers for IP pools, addresses, routes, and WireGuard configuration data. It also manages a registry of OLTs (imported from browser bookmarks) and maps them to routers.

## Glossary

- **IPAM_Module**: The integrated IPAM (IP Address Management) feature within the Netking.id admin panel, replacing the standalone IP Manager app
- **IpamRouter**: A MikroTik router device discovered and scanned by the IPAM module, identified by its WireGuard IP address (uses `ipam_routers` table to avoid conflict with existing system)
- **IpamOlt**: A lightweight OLT reference used for router-to-OLT mapping in IPAM context (uses `ipam_olts` table, distinct from the existing `olts` table which stores full OLT hardware with SNMP/SSH)
- **IpPool**: A named IP address pool configured on a MikroTik router
- **RouterAddress**: An IP address assigned to an interface on a MikroTik router
- **RouterRoute**: A static route entry on a MikroTik router
- **WireguardInterface**: A WireGuard interface configured on a MikroTik router
- **WireguardPeer**: A WireGuard peer entry configured on a MikroTik router
- **Subnet**: A managed IP subnet (network_address/prefix_length) tracked by the IPAM module
- **IpamAuditLog**: A timestamped record of actions performed within the IPAM module
- **RouterOS_REST_API**: The HTTP REST API exposed by MikroTik RouterOS v7 used to query router configuration
- **Admin_User**: An authenticated user with the admin role in the Netking.id system

## Requirements

### Requirement 1: Database Migration

**User Story:** As a developer, I want to migrate IPAM data tables from SQLite to MySQL in the Netking.id database, so that all data resides in a single managed database.

#### Acceptance Criteria

1. THE IPAM_Module SHALL create MySQL tables: `ipam_routers`, `ipam_olts`, `ipam_ip_pools`, `ipam_router_addresses`, `ipam_router_routes`, `ipam_wireguard_interfaces`, `ipam_wireguard_peers`, `ipam_subnets`, and `ipam_audit_logs`
2. THE IPAM_Module SHALL preserve all foreign key relationships from the original schema (router_id references in pools, addresses, routes, wireguard tables; mapped_olt_id in routers)
3. THE IPAM_Module SHALL use Laravel timestamp columns (nullable `created_at`, `updated_at`) instead of string-based date columns from the original SQLite schema
4. THE IPAM_Module SHALL prefix all IPAM tables with `ipam_` to avoid naming conflicts with existing Netking.id tables (specifically the existing `olts` table)
5. THE IPAM_Module SHALL include a data migration command that reads the existing SQLite database file and imports all records into the new MySQL tables

### Requirement 2: Eloquent Models

**User Story:** As a developer, I want Eloquent models for all IPAM entities, so that the application can interact with IPAM data using Laravel conventions.

#### Acceptance Criteria

1. THE IPAM_Module SHALL provide models: `IpamRouter`, `IpamOlt`, `IpamIpPool`, `IpamRouterAddress`, `IpamRouterRoute`, `IpamWireguardInterface`, `IpamWireguardPeer`, `IpamSubnet`, and `IpamAuditLog`
2. THE IpamRouter model SHALL define relationships: hasMany IpamIpPool, hasMany IpamRouterAddress, hasMany IpamRouterRoute, hasMany IpamWireguardInterface, hasMany IpamWireguardPeer, and belongsTo IpamOlt (via mapped_olt_id)
3. THE IPAM_Module SHALL store all models in the `App\Models\Ipam` namespace to keep them organized separately from existing models
4. THE IpamRouter model SHALL encrypt the `auth_password` field at rest using Laravel's built-in encryption

### Requirement 3: MikroTik Router Scanning

**User Story:** As an admin, I want to scan MikroTik routers via RouterOS REST API over WireGuard, so that I can discover IP pools, addresses, routes, and WireGuard configuration automatically.

#### Acceptance Criteria

1. WHEN an Admin_User triggers a scan for a specific IpamRouter, THE IPAM_Module SHALL connect to the router via RouterOS_REST_API using the stored WireGuard IP and credentials
2. WHEN the scan completes successfully, THE IPAM_Module SHALL store discovered IP pools, addresses, routes, WireGuard interfaces, and WireGuard peers in the corresponding database tables
3. WHEN the scan completes successfully, THE IPAM_Module SHALL update the IpamRouter connection_status to "connected" and record last_scanned_at timestamp
4. IF a scan fails due to connection timeout or authentication error, THEN THE IPAM_Module SHALL update the IpamRouter connection_status to "error" and store the error message in last_error
5. WHEN an Admin_User triggers a bulk scan, THE IPAM_Module SHALL scan all registered routers with a configurable concurrency limit (default: 8 concurrent scans)
6. THE IPAM_Module SHALL enforce a configurable cooldown period (default: 20 seconds) between consecutive scans of the same router
7. THE IPAM_Module SHALL use HTTPS or HTTP for RouterOS REST API connections based on the `MIKROTIK_USE_HTTPS` configuration setting
8. THE IPAM_Module SHALL allow insecure TLS connections when `MIKROTIK_ALLOW_INSECURE_TLS` is enabled (for self-signed certificates on routers)

### Requirement 4: OLT Management and Bookmark Import

**User Story:** As an admin, I want to manage OLT references and import them from browser bookmark exports, so that I can quickly populate the OLT registry from existing documentation.

#### Acceptance Criteria

1. THE IPAM_Module SHALL provide CRUD operations for IpamOlt records (name and ip_address fields)
2. WHEN an Admin_User uploads an HTML bookmark file, THE IPAM_Module SHALL parse bookmark entries and create IpamOlt records for each entry containing an IP address
3. WHEN importing bookmarks, THE IPAM_Module SHALL skip entries where the ip_address already exists in the ipam_olts table
4. WHEN an IpamOlt is created or imported, THE IPAM_Module SHALL record the action in the IpamAuditLog

### Requirement 5: Router-to-OLT Mapping

**User Story:** As an admin, I want to map routers to their associated OLTs, so that I can see which router serves which OLT location.

#### Acceptance Criteria

1. WHEN an Admin_User assigns an IpamOlt to an IpamRouter, THE IPAM_Module SHALL update the router's mapped_olt_id field
2. THE IPAM_Module SHALL provide auto-mapping functionality that matches routers to OLTs based on IP address patterns in the router's interface addresses
3. WHEN auto-mapping is performed, THE IPAM_Module SHALL record the mapping action in the IpamAuditLog with both the router and OLT identifiers

### Requirement 6: Subnet Management

**User Story:** As an admin, I want to manage IP subnets with utilization tracking, so that I can plan IP address allocation across the network.

#### Acceptance Criteria

1. THE IPAM_Module SHALL provide CRUD operations for IpamSubnet records (network_address, prefix_length, name, description, vlan_id, location)
2. THE IPAM_Module SHALL enforce uniqueness on the network_address field
3. WHEN an Admin_User requests subnet utilization, THE IPAM_Module SHALL calculate the percentage of used IPs by comparing subnet ranges against discovered router addresses and IP pool ranges
4. WHEN an Admin_User requests subnet suggestions, THE IPAM_Module SHALL identify available address space within existing subnets that is not yet allocated
5. WHEN a subnet is created, updated, or deleted, THE IPAM_Module SHALL record the action in the IpamAuditLog

### Requirement 7: WireGuard Visibility

**User Story:** As an admin, I want to view WireGuard interface and peer details for each router, so that I can monitor tunnel connectivity.

#### Acceptance Criteria

1. WHEN an Admin_User views a router's detail page, THE IPAM_Module SHALL display all WireGuard interfaces with name, listen_port, public_key, disabled status, and comment
2. WHEN an Admin_User views a router's detail page, THE IPAM_Module SHALL display all WireGuard peers with interface_name, public_key, allowed_address, endpoint_address, endpoint_port, disabled status, and comment

### Requirement 8: Audit Logging

**User Story:** As an admin, I want a complete audit trail of all IPAM actions, so that I can track who made changes and when.

#### Acceptance Criteria

1. THE IPAM_Module SHALL log all data-modifying operations (create, update, delete, scan, import, map) to the ipam_audit_logs table
2. THE IpamAuditLog SHALL record: actor (authenticated admin username), action (operation type), target_type (entity type), target_id (entity identifier), detail (human-readable description), and created_at timestamp
3. WHEN an Admin_User views the audit log, THE IPAM_Module SHALL display entries in reverse chronological order with pagination

### Requirement 9: Data Export

**User Story:** As an admin, I want to export router explorer data to CSV, so that I can share network inventory information externally.

#### Acceptance Criteria

1. WHEN an Admin_User triggers a CSV export, THE IPAM_Module SHALL generate a downloadable CSV file containing all IpamRouter records with their mapped OLT names and connection status
2. THE CSV export SHALL include columns: device_name, wireguard_ip, connection_status, mapped_olt_name, ip_pools, last_scanned_at

### Requirement 10: Admin UI Integration

**User Story:** As an admin, I want IPAM features accessible from the existing Netking.id sidebar, so that I can manage network inventory without switching applications.

#### Acceptance Criteria

1. THE IPAM_Module SHALL add an "IPAM" section to the admin sidebar with menu items: Dashboard, Routers, Subnets, and Audit Log
2. THE IPAM_Module SHALL restrict the IPAM sidebar section to Admin_User role only
3. THE IPAM_Module SHALL use Blade templates following the existing Netking.id page structure: ms-page > ms-page-head > ms-panel > ms-table-shell pattern
4. THE IPAM_Module SHALL use the existing Tabler-inspired CSS framework (not Tailwind) for all UI components
5. THE IPAM_Module SHALL use Boxicons (bx class prefix) for menu icons consistent with the existing sidebar

### Requirement 11: Authentication Integration

**User Story:** As an admin, I want IPAM features protected by the existing Netking.id authentication, so that there is no separate login or token management.

#### Acceptance Criteria

1. THE IPAM_Module SHALL use the existing Netking.id session-based authentication (auth middleware) for all IPAM routes
2. THE IPAM_Module SHALL use the existing `admin` middleware to restrict access to admin-role users only
3. THE IPAM_Module SHALL remove the standalone token-based authentication from the original IP Manager

### Requirement 12: MikroTik Connection Settings

**User Story:** As an admin, I want to configure MikroTik connection parameters from the admin panel, so that I can adjust timeouts, credentials, and protocol settings.

#### Acceptance Criteria

1. THE IPAM_Module SHALL provide a settings page for MikroTik connection parameters: default username, default password, use_https flag, allow_insecure_tls flag, request_timeout_secs, max_scan_concurrency, and scan_cooldown_secs
2. THE IPAM_Module SHALL store connection settings in the existing `settings` table using a namespaced key format (e.g., `ipam.mikrotik_username`)
3. WHEN connection settings are updated, THE IPAM_Module SHALL record the change in the IpamAuditLog (without logging password values)

### Requirement 13: Router Online Status Monitoring

**User Story:** As an admin, I want to see which routers are currently reachable, so that I can identify connectivity issues quickly.

#### Acceptance Criteria

1. THE IPAM_Module SHALL display an online/offline indicator for each IpamRouter based on the is_online field
2. WHEN an Admin_User triggers a scan or rescan, THE IPAM_Module SHALL update the is_online and last_ping_at fields based on whether the router responds to the REST API health check
3. THE IPAM_Module dashboard SHALL display summary statistics: total routers, connected count, error count, and total subnets defined

### Requirement 14: Decommission Readiness

**User Story:** As a developer, I want the migration to be fully self-contained in the Netking.id codebase, so that CT 100 can be safely deleted after deployment.

#### Acceptance Criteria

1. THE IPAM_Module SHALL not depend on any external service running on CT 100 after migration is complete
2. THE IPAM_Module SHALL include a WireGuard network configuration that allows VM 103 to reach the MikroTik routers directly (documenting required WireGuard peer setup on VM 103)
3. THE IPAM_Module SHALL provide a verification checklist command (artisan ipam:verify) that confirms all data has been migrated and all routers are reachable from VM 103
