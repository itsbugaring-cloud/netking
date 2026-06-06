# Implementation Plan: IPAM Integration

## Overview

Migrate the standalone Netking IP Manager into the Netking.id admin panel as an embedded IPAM module. Implementation follows a bottom-up approach: database schema first, then models, services, controller/routes, views, sidebar integration, and artisan commands.

## Tasks

- [x] 1. Database migration and Eloquent models
  - [x] 1.1 Create database migration for all IPAM tables
    - Create a single migration file for all 9 `ipam_*` tables
    - Define `ipam_routers` with all columns, encrypted auth_password, unique wireguard_ip
    - Define `ipam_olts` with unique name and ip_address
    - Define `ipam_ip_pools` with composite unique (router_id, pool_name)
    - Define `ipam_router_addresses`, `ipam_router_routes`
    - Define `ipam_wireguard_interfaces` with composite unique (router_id, name)
    - Define `ipam_wireguard_peers`
    - Define `ipam_subnets` with unique network_address
    - Define `ipam_audit_logs` with only created_at (no updated_at)
    - Add foreign keys with cascadeOnDelete for all router_id and mapped_olt_id references
    - _Requirements: 1.1, 1.2, 1.3, 1.4_

  - [x] 1.2 Create IpamRouter model
    - Create `app/Models/Ipam/IpamRouter.php`
    - Define table, fillable, casts (auth_password → encrypted, is_online → boolean, timestamps → datetime)
    - Define relationships: hasMany pools, addresses, routes, wireguard interfaces, wireguard peers; belongsTo IpamOlt
    - _Requirements: 2.1, 2.2, 2.3, 2.4_

  - [x] 1.3 Create IpamOlt model
    - Create `app/Models/Ipam/IpamOlt.php`
    - Define table, fillable (name, ip_address), relationship hasMany IpamRouter
    - _Requirements: 2.1, 2.3_

  - [x] 1.4 Create remaining Eloquent models
    - Create `app/Models/Ipam/IpamIpPool.php` with belongsTo IpamRouter
    - Create `app/Models/Ipam/IpamRouterAddress.php` with belongsTo IpamRouter, disabled cast
    - Create `app/Models/Ipam/IpamRouterRoute.php` with belongsTo IpamRouter, disabled cast
    - Create `app/Models/Ipam/IpamWireguardInterface.php` with belongsTo IpamRouter, disabled cast
    - Create `app/Models/Ipam/IpamWireguardPeer.php` with belongsTo IpamRouter, disabled cast
    - Create `app/Models/Ipam/IpamSubnet.php` with fillable fields
    - Create `app/Models/Ipam/IpamAuditLog.php` with $timestamps = false, only created_at
    - _Requirements: 2.1, 2.2, 2.3_

- [~] 2. Checkpoint - Run migration and verify models
  - Ensure migration runs without errors, ask the user if questions arise.

- [x] 3. Service layer implementation
  - [x] 3.1 Create IpamAuditService
    - Create `app/Services/Ipam/IpamAuditService.php`
    - Implement static `log(action, targetType, targetId, detail)` method
    - Auto-capture authenticated admin username as actor
    - _Requirements: 8.1, 8.2_

  - [x] 3.2 Create MikroTikScannerService
    - Create `app/Services/Ipam/MikroTikScannerService.php`
    - Implement `buildHttpClient()` using Laravel Http facade with settings from Setting model (ipam.* keys)
    - Implement `canScan()` with cooldown check against last_scanned_at
    - Implement `healthCheck()` to verify router connectivity
    - Implement private fetch methods: `fetchIpPools()`, `fetchAddresses()`, `fetchRoutes()`, `fetchWireguardInterfaces()`, `fetchWireguardPeers()`
    - Implement `scanRouter()` that orchestrates all fetches, stores results, updates connection_status/last_scanned_at/is_online
    - Implement `scanAll()` with configurable concurrency using Laravel HTTP pool
    - Handle errors: timeout, auth failure, invalid JSON, TLS errors — store in last_error
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 13.2_

  - [x] 3.3 Create BookmarkParserService
    - Create `app/Services/Ipam/BookmarkParserService.php`
    - Implement `parse(htmlContent)` to extract name + IP from Netscape bookmark format
    - Implement `importToDatabase(entries, actor)` to create IpamOlt records, skip duplicates
    - Record audit log entries for each imported OLT
    - _Requirements: 4.2, 4.3, 4.4_

  - [x] 3.4 Create SubnetUtilizationService
    - Create `app/Services/Ipam/SubnetUtilizationService.php`
    - Implement `calculateUtilization(subnet)`: compute total usable IPs, count used IPs from addresses + pool ranges
    - Implement `findAvailableSpace(subnet)`: identify gaps in allocations
    - Implement `calculateAll()`: aggregate utilization for all subnets
    - _Requirements: 6.3, 6.4_

- [~] 4. Checkpoint - Verify services compile correctly
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Controller and routes
  - [x] 5.1 Create IpamController with dependency injection
    - Create `app/Http/Controllers/Admin/IpamController.php`
    - Inject MikroTikScannerService, BookmarkParserService, SubnetUtilizationService in constructor
    - Implement `dashboard()` with summary stats (total routers, connected, error, subnets)
    - _Requirements: 10.3, 13.3_

  - [x] 5.2 Implement router explorer actions in IpamController
    - Implement `routers()` with search/filter support
    - Implement `routerDetail()` showing pools, addresses, routes, WireGuard data
    - Implement `scanRouter()` with cooldown enforcement
    - Implement `scanAll()` with concurrency limit
    - Implement `exportCsv()` with all required columns
    - Implement `mapOlt()` and `autoMap()` for router-to-OLT mapping
    - Add validation and audit logging for all data-modifying actions
    - _Requirements: 3.1, 3.5, 3.6, 5.1, 5.2, 5.3, 7.1, 7.2, 9.1, 9.2_

  - [x] 5.3 Implement OLT management actions in IpamController
    - Implement `olts()` list view with search
    - Implement `storeOlt()`, `updateOlt()`, `destroyOlt()` with validation
    - Implement `importBookmarks()` accepting HTML file upload
    - Add audit logging for all OLT operations
    - _Requirements: 4.1, 4.2, 4.3, 4.4_

  - [x] 5.4 Implement subnet management actions in IpamController
    - Implement `subnets()` list view
    - Implement `storeSubnet()`, `updateSubnet()`, `destroySubnet()` with validation (valid CIDR, unique network_address)
    - Implement `subnetUtilization()` returning JSON
    - Implement `subnetSuggestions()` returning JSON
    - Add audit logging for all subnet operations
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

  - [x] 5.5 Implement audit log and settings actions in IpamController
    - Implement `auditLog()` with reverse-chronological pagination
    - Implement `settings()` reading from Setting model with ipam.* keys
    - Implement `updateSettings()` storing values in settings table, audit log without password values
    - _Requirements: 8.3, 12.1, 12.2, 12.3_

  - [x] 5.6 Register IPAM routes
    - Add route group in `routes/web.php` under `admin/ipam` prefix with `auth` + `admin` middleware
    - Define all routes matching the route structure in the design document
    - _Requirements: 11.1, 11.2_

- [~] 6. Checkpoint - Verify routes register without errors
  - Ensure all tests pass, ask the user if questions arise.

- [x] 7. Blade views
  - [x] 7.1 Create IPAM dashboard view
    - Create `resources/views/admin/ipam/dashboard.blade.php`
    - Follow ms-page > ms-page-head > ms-panel pattern
    - Display summary cards: total routers, connected, errors, total subnets
    - Use Tabler-inspired CSS framework and Boxicons
    - _Requirements: 10.3, 10.4, 10.5, 13.3_

  - [x] 7.2 Create router explorer views
    - Create `resources/views/admin/ipam/routers/index.blade.php` — router list with status indicators, search, scan-all button, export CSV button
    - Create `resources/views/admin/ipam/routers/detail.blade.php` — router detail with IP pools table, addresses table, routes table, WireGuard interfaces/peers tables, OLT mapping dropdown
    - Use ms-table-shell pattern for all data tables
    - Show online/offline indicator per router
    - _Requirements: 7.1, 7.2, 9.1, 10.3, 10.4, 13.1_

  - [x] 7.3 Create OLT management view
    - Create `resources/views/admin/ipam/olts/index.blade.php`
    - Include OLT list table, inline create/edit forms, bookmark import upload form
    - _Requirements: 4.1, 10.3, 10.4_

  - [x] 7.4 Create subnet management view
    - Create `resources/views/admin/ipam/subnets/index.blade.php`
    - Include subnet list table with utilization percentage bars, create/edit forms
    - _Requirements: 6.1, 6.3, 10.3, 10.4_

  - [x] 7.5 Create audit log view
    - Create `resources/views/admin/ipam/audit-log.blade.php`
    - Display paginated audit log table in reverse chronological order
    - _Requirements: 8.3, 10.3_

  - [x] 7.6 Create settings view
    - Create `resources/views/admin/ipam/settings.blade.php`
    - Form for all MikroTik connection parameters with current values pre-filled
    - _Requirements: 12.1, 10.3, 10.4_

- [x] 8. Sidebar integration
  - [x] 8.1 Add IPAM section to admin sidebar
    - Modify the admin sidebar partial (likely `resources/views/layouts/partials/sidebar.blade.php` or equivalent)
    - Add "IPAM" section with Boxicons icon (bx class prefix)
    - Add menu items: Dashboard, Routers, Subnets, Audit Log
    - Ensure section only visible to admin-role users
    - _Requirements: 10.1, 10.2, 10.5_

- [ ] 9. Artisan commands
  - [~] 9.1 Create ipam:migrate-data command
    - Create `app/Console/Commands/IpamMigrateData.php`
    - Accept SQLite file path as argument
    - Read all tables from SQLite and insert into MySQL ipam_* tables
    - Convert string timestamps to proper datetime format
    - Display progress and summary (records migrated per table)
    - _Requirements: 1.5, 14.1_

  - [~] 9.2 Create ipam:verify command
    - Create `app/Console/Commands/IpamVerify.php`
    - Check record counts match between source and destination
    - Test connectivity to each router from VM 103
    - Display pass/fail checklist for each verification step
    - _Requirements: 14.3_

- [~] 10. Checkpoint - Full integration verification
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 11. Testing
  - [ ]* 11.1 Write property test for password encryption round-trip
    - **Property 2: Password Encryption Round-Trip**
    - **Validates: Requirements 2.4**

  - [ ]* 11.2 Write property test for scan cooldown enforcement
    - **Property 4: Scan Cooldown Enforcement**
    - **Validates: Requirements 3.6**

  - [ ]* 11.3 Write property test for bookmark parsing
    - **Property 5: Bookmark Parsing Extracts All IPs**
    - **Validates: Requirements 4.2**

  - [ ]* 11.4 Write property test for bookmark import idempotency
    - **Property 6: Bookmark Import Idempotency**
    - **Validates: Requirements 4.3**

  - [ ]* 11.5 Write property test for subnet uniqueness enforcement
    - **Property 8: Subnet Uniqueness Enforcement**
    - **Validates: Requirements 6.2**

  - [ ]* 11.6 Write property test for subnet utilization calculation
    - **Property 9: Subnet Utilization Calculation Correctness**
    - **Validates: Requirements 6.3**

  - [ ]* 11.7 Write property test for subnet suggestions
    - **Property 10: Subnet Suggestions Return Only Unallocated Space**
    - **Validates: Requirements 6.4**

  - [ ]* 11.8 Write property test for audit log completeness
    - **Property 11: All Data-Modifying Operations Create Audit Log**
    - **Validates: Requirements 8.1**

  - [ ]* 11.9 Write property test for CSV export completeness
    - **Property 12: CSV Export Contains All Routers**
    - **Validates: Requirements 9.1, 9.2**

  - [ ]* 11.10 Write property test for audit log password exclusion
    - **Property 13: Audit Log Never Contains Passwords**
    - **Validates: Requirements 12.3**

  - [ ]* 11.11 Write unit tests for IpamController authorization
    - Test non-admin users receive 403 on all IPAM routes
    - Test admin users can access all IPAM routes
    - _Requirements: 11.1, 11.2_

  - [ ]* 11.12 Write unit tests for OLT CRUD operations
    - Test create, read, update, delete with specific examples
    - Test validation (unique ip_address, required fields)
    - _Requirements: 4.1_

  - [ ]* 11.13 Write integration tests for MikroTik scanning
    - Mock HTTP responses simulating RouterOS v7 REST output
    - Test successful scan stores all entities correctly
    - Test error handling (timeout, auth failure, invalid JSON)
    - Test bulk scan respects concurrency limit
    - _Requirements: 3.1, 3.2, 3.4, 3.5_

- [~] 12. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- Unit tests validate specific examples and edge cases
- All models use `App\Models\Ipam` namespace to avoid conflicts
- All tables use `ipam_` prefix to avoid conflicts with existing schema
- Views follow the established ms-page > ms-panel > ms-table-shell pattern
- PHP/Laravel is the implementation language throughout

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["1.2", "1.3", "1.4"] },
    { "id": 2, "tasks": ["3.1"] },
    { "id": 3, "tasks": ["3.2", "3.3", "3.4"] },
    { "id": 4, "tasks": ["5.1", "5.3", "5.4"] },
    { "id": 5, "tasks": ["5.2", "5.5", "5.6"] },
    { "id": 6, "tasks": ["7.1", "7.3", "7.4", "7.5", "7.6"] },
    { "id": 7, "tasks": ["7.2", "8.1"] },
    { "id": 8, "tasks": ["9.1", "9.2"] },
    { "id": 9, "tasks": ["11.1", "11.2", "11.3", "11.4", "11.5"] },
    { "id": 10, "tasks": ["11.6", "11.7", "11.8", "11.9", "11.10"] },
    { "id": 11, "tasks": ["11.11", "11.12", "11.13"] }
  ]
}
```
