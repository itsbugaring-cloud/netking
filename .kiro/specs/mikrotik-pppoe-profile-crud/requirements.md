# Requirements Document

## Introduction

This feature adds full CRUD (Create, Read, Update, Delete) management for MikroTik PPPoE profiles directly from the Netking ISP admin panel. Currently, Netking can only read profiles from the router and sync them to local Package records. Admins must manually SSH or use Winbox to create, edit, or delete profiles. This feature eliminates that manual step by providing a web-based interface that writes directly to the MikroTik RouterOS API and automatically synchronizes changes to the local Netking Package records.

## Glossary

- **Admin_Panel**: The Netking web-based administration interface used by ISP operators
- **MikroTik_Router**: A RouterOS-based network device managed per area, connected via RouterOS API on port 8728
- **PPPoE_Profile**: A RouterOS configuration object at `/ppp/profile` that defines connection parameters (rate-limit, addresses, DNS) for PPPoE subscribers
- **Package**: A local Netking database record representing an internet service tier, linked to a MikroTik PPPoE_Profile via the `mikrotik_profile` field
- **Rate_Limit**: A MikroTik speed specification in the format "upload/download" (e.g., "5M/10M" means 5 Mbps upload, 10 Mbps download)
- **Area**: A geographic service zone in Netking, each associated with its own MikroTik_Router
- **PPPoE_Secret**: A RouterOS subscriber account at `/ppp/secret` that references a PPPoE_Profile
- **Profile_Template**: A pre-defined speed tier configuration (e.g., 5M/10M, 10M/20M) that can be used for quick profile creation
- **RouterOS_API**: The programmatic interface to MikroTik routers using the `routeros-api` PHP library on port 8728

## Requirements

### Requirement 1: List PPPoE Profiles with Full Detail

**User Story:** As an admin, I want to view all PPPoE profiles on a selected router with full detail, so that I can assess existing service tiers before making changes.

#### Acceptance Criteria

1. WHEN the admin selects an Area from the profile management page, THE Admin_Panel SHALL retrieve all PPPoE_Profile records from the MikroTik_Router for that Area using the `/ppp/profile/print` endpoint
2. THE Admin_Panel SHALL display each PPPoE_Profile with its name, rate-limit, local-address, remote-address, dns-server, change-tcp-mss, and only-one fields
3. WHEN the Admin_Panel displays PPPoE_Profile records, THE Admin_Panel SHALL show the number of PPPoE_Secret records currently referencing each profile
4. IF the MikroTik_Router connection fails, THEN THE Admin_Panel SHALL display the connection error message and disable create/edit/delete actions
5. THE Admin_Panel SHALL exclude the "default" and "default-encryption" profiles from edit and delete actions

### Requirement 2: Create PPPoE Profile on Router

**User Story:** As an admin, I want to create a new PPPoE profile on the router from the admin panel, so that I can define new service tiers without SSH or Winbox access.

#### Acceptance Criteria

1. WHEN the admin submits a valid profile creation form, THE Admin_Panel SHALL send a `/ppp/profile/add` command to the MikroTik_Router with the specified parameters
2. THE Admin_Panel SHALL require the profile name field and the rate-limit field for profile creation
3. THE Admin_Panel SHALL accept the following optional fields for profile creation: local-address, remote-address, dns-server, change-tcp-mss, only-one, burst-limit, burst-threshold, burst-time
4. THE Admin_Panel SHALL validate that the Rate_Limit follows the format "uploadM/downloadM" where upload and download are positive integers
5. WHEN the admin enters a profile name, THE Admin_Panel SHALL validate that the name contains only alphanumeric characters, hyphens, and underscores
6. IF a PPPoE_Profile with the same name already exists on the MikroTik_Router, THEN THE Admin_Panel SHALL reject the creation and display a duplicate name error
7. WHEN the MikroTik_Router confirms successful profile creation, THE Admin_Panel SHALL log the creation event with the admin username, profile name, and area

### Requirement 3: Edit PPPoE Profile on Router

**User Story:** As an admin, I want to modify existing PPPoE profile parameters on the router, so that I can adjust speed tiers and connection settings for subscribers.

#### Acceptance Criteria

1. WHEN the admin submits a valid profile edit form, THE Admin_Panel SHALL send a `/ppp/profile/set` command to the MikroTik_Router with the modified parameters
2. THE Admin_Panel SHALL allow modification of the following fields: rate-limit, local-address, remote-address, dns-server, change-tcp-mss, only-one, burst-limit, burst-threshold, burst-time
3. THE Admin_Panel SHALL prevent modification of the profile name field on existing profiles
4. THE Admin_Panel SHALL pre-populate the edit form with current profile values retrieved from the MikroTik_Router
5. WHEN the MikroTik_Router confirms successful profile update, THE Admin_Panel SHALL log the update event with the admin username, profile name, changed fields, and area
6. IF the `/ppp/profile/set` command fails, THEN THE Admin_Panel SHALL display the RouterOS error message and preserve the form data for retry

### Requirement 4: Delete PPPoE Profile from Router

**User Story:** As an admin, I want to remove unused PPPoE profiles from the router, so that I can keep the profile list clean and manageable.

#### Acceptance Criteria

1. WHEN the admin confirms profile deletion, THE Admin_Panel SHALL send a `/ppp/profile/remove` command to the MikroTik_Router
2. WHEN the admin requests profile deletion, THE Admin_Panel SHALL check the number of PPPoE_Secret records referencing the target profile
3. IF one or more PPPoE_Secret records reference the target profile, THEN THE Admin_Panel SHALL reject the deletion and display the count of assigned subscribers
4. THE Admin_Panel SHALL require explicit confirmation before executing profile deletion
5. WHEN the MikroTik_Router confirms successful profile deletion, THE Admin_Panel SHALL log the deletion event with the admin username, profile name, and area
6. IF the `/ppp/profile/remove` command fails, THEN THE Admin_Panel SHALL display the RouterOS error message to the admin

### Requirement 5: Two-Way Sync with Local Package Records

**User Story:** As an admin, I want profile changes made on the router to automatically reflect in the local Package database, so that billing and customer records stay consistent.

#### Acceptance Criteria

1. WHEN the MikroTik_Router confirms successful profile creation, THE Admin_Panel SHALL create a corresponding Package record with the profile name as `mikrotik_profile`, parsed speed values from the Rate_Limit, and the correct Area association
2. WHEN the MikroTik_Router confirms successful profile update, THE Admin_Panel SHALL update the corresponding Package record speed_down and speed_up fields to match the new Rate_Limit values
3. WHEN the MikroTik_Router confirms successful profile deletion, THE Admin_Panel SHALL deactivate (set `is_active` to false) the corresponding Package record instead of deleting it
4. WHEN creating a new Package record from profile creation, THE Admin_Panel SHALL assign a default price based on the speed tier using the `billing.default_speed_prices` configuration
5. WHEN creating a new Package record from profile creation, THE Admin_Panel SHALL generate the package code using the format: uppercase profile name with spaces replaced by hyphens, followed by hyphen and area ID
6. IF no corresponding Package record exists during profile update, THEN THE Admin_Panel SHALL create a new Package record with the updated profile parameters

### Requirement 6: Profile Templates for Quick Creation

**User Story:** As an admin, I want to use pre-defined speed tier templates to quickly create profiles, so that I can standardize service offerings across routers without manually entering parameters each time.

#### Acceptance Criteria

1. THE Admin_Panel SHALL provide the following Profile_Template options: 5M/10M, 10M/20M, 20M/50M, 50M/100M
2. WHEN the admin selects a Profile_Template, THE Admin_Panel SHALL auto-populate the creation form with the template upload and download speeds in the Rate_Limit format
3. WHEN the admin selects a Profile_Template, THE Admin_Panel SHALL generate a default profile name based on the speed values (e.g., "10M-20M")
4. THE Admin_Panel SHALL allow the admin to modify any auto-populated field from a Profile_Template before submission
5. WHERE the admin configures custom Profile_Template values in application settings, THE Admin_Panel SHALL use those custom templates instead of the defaults

### Requirement 7: Input Validation and Error Handling

**User Story:** As an admin, I want clear validation feedback and error messages, so that I can correct mistakes before they reach the router.

#### Acceptance Criteria

1. THE Admin_Panel SHALL validate all profile fields client-side before submitting to the server
2. THE Admin_Panel SHALL validate that burst-limit follows the same "uploadM/downloadM" format as Rate_Limit
3. WHEN burst-limit is specified, THE Admin_Panel SHALL require burst-threshold and burst-time to also be specified
4. THE Admin_Panel SHALL validate that change-tcp-mss accepts only "yes" or "no" values
5. THE Admin_Panel SHALL validate that only-one accepts only "yes", "no", or "default" values
6. IF the MikroTik_Router returns an error for any CRUD operation, THEN THE Admin_Panel SHALL display the original RouterOS error message alongside a user-friendly explanation
7. IF the MikroTik_Router connection times out during a CRUD operation, THEN THE Admin_Panel SHALL display a timeout message and advise the admin to verify the router state manually
