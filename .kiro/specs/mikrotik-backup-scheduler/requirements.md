# Requirements Document

## Introduction

The MikroTik Backup Scheduler feature adds automated and manual backup capabilities to the Netking ISP admin panel. Currently, Netking manages multiple MikroTik routers across different areas but has no backup functionality. If a router fails or its configuration is lost, there is no restore point. This feature enables administrators to create on-demand backups, schedule automatic backups, download backup files, manage backup history, and enforce retention policies — ensuring router configurations are always recoverable.

## Glossary

- **Backup_Scheduler**: The system component responsible for managing scheduled and manual backups of MikroTik router configurations within the Netking admin panel.
- **Binary_Backup**: A MikroTik `.backup` file created via `/system/backup/save` that contains the full router configuration including passwords and certificates in a non-human-readable format.
- **Text_Export**: A MikroTik `.rsc` file generated via the `/export` CLI command that contains router configuration in human-readable script format, excluding certificates and passwords.
- **Router**: A MikroTik RouterOS device associated with an Area in the Netking system, identified by its IP address, username, and password stored in the Area model.
- **Retention_Policy**: A configurable rule that determines how long backup files are kept before automatic deletion.
- **Backup_Storage**: The local server directory (`storage/app/backups/{router_id}/`) where backup files are persisted.
- **Admin**: An authenticated user with administrative privileges in the Netking panel.

## Requirements

### Requirement 1: Manual Backup Creation

**User Story:** As an Admin, I want to trigger an immediate backup of any router, so that I can create a restore point before making configuration changes.

#### Acceptance Criteria

1. WHEN the Admin initiates a manual backup for a Router, THE Backup_Scheduler SHALL create a backup of the specified type (Binary_Backup or Text_Export) on the Router filesystem via the RouterOS API.
2. WHEN the Router backup file is created successfully, THE Backup_Scheduler SHALL download the file from the Router and store it in the Backup_Storage directory.
3. WHEN the backup file is stored successfully, THE Backup_Scheduler SHALL record the backup metadata (router_id, filename, type, size_bytes, created_at) in the database.
4. WHEN the backup file is stored successfully, THE Backup_Scheduler SHALL delete the temporary backup file from the Router filesystem to conserve Router disk space.
5. IF the Router is unreachable during a manual backup attempt, THEN THE Backup_Scheduler SHALL return an error message indicating the connection failure and the Router identity.
6. IF the Router filesystem has insufficient disk space for the backup file, THEN THE Backup_Scheduler SHALL return an error message indicating insufficient disk space.

### Requirement 2: Scheduled Automatic Backups

**User Story:** As an Admin, I want backups to run automatically on a configurable schedule, so that all routers have recent restore points without manual intervention.

#### Acceptance Criteria

1. THE Backup_Scheduler SHALL support configurable schedule frequencies: daily and weekly.
2. WHEN the configured schedule time is reached, THE Backup_Scheduler SHALL initiate a backup for each registered Router sequentially.
3. THE Backup_Scheduler SHALL store the schedule configuration (frequency, time_of_day, backup_type, enabled status) in the application settings.
4. WHEN a scheduled backup fails for a specific Router, THE Backup_Scheduler SHALL log the failure with the Router identity and error details, and continue processing remaining Routers.
5. THE Backup_Scheduler SHALL execute scheduled backups without overlapping with a previously running backup job.
6. WHEN the Admin changes the schedule configuration, THE Backup_Scheduler SHALL apply the new schedule starting from the next scheduled execution.

### Requirement 3: Backup File Download

**User Story:** As an Admin, I want to download backup files from the admin panel, so that I can store copies externally or use them for restoring a router.

#### Acceptance Criteria

1. WHEN the Admin requests to download a specific backup file, THE Backup_Scheduler SHALL stream the file from Backup_Storage to the Admin browser as a download.
2. THE Backup_Scheduler SHALL set the appropriate Content-Type and Content-Disposition headers for the downloaded file based on backup type (.backup or .rsc).
3. WHEN the Admin requests a backup file that no longer exists in Backup_Storage, THE Backup_Scheduler SHALL return a 404 error with a descriptive message.
4. THE Backup_Scheduler SHALL stream large backup files to the Admin browser without loading the entire file into memory.

### Requirement 4: Backup History

**User Story:** As an Admin, I want to view the backup history for each router, so that I can verify backups are running correctly and identify available restore points.

#### Acceptance Criteria

1. WHEN the Admin views the backup history for a Router, THE Backup_Scheduler SHALL display a list of all stored backups for that Router sorted by creation date descending.
2. THE Backup_Scheduler SHALL display the following metadata for each backup entry: filename, backup type (binary or text), file size in human-readable format, and creation timestamp.
3. WHEN the Admin views the backup overview page, THE Backup_Scheduler SHALL display a summary showing last backup date and total backup count per Router.
4. THE Backup_Scheduler SHALL paginate the backup history list when the number of entries exceeds 20 per page.

### Requirement 5: Retention Policy

**User Story:** As an Admin, I want old backups to be automatically deleted after a configurable number of days, so that disk space is managed without manual cleanup.

#### Acceptance Criteria

1. THE Backup_Scheduler SHALL enforce a configurable retention period with a default value of 30 days.
2. WHEN the retention cleanup runs, THE Backup_Scheduler SHALL delete backup files from Backup_Storage that are older than the configured retention period.
3. WHEN the retention cleanup deletes a backup file, THE Backup_Scheduler SHALL remove the corresponding database record.
4. THE Backup_Scheduler SHALL execute the retention cleanup process daily.
5. WHEN the Admin changes the retention period setting, THE Backup_Scheduler SHALL apply the new retention period on the next cleanup execution.
6. THE Backup_Scheduler SHALL retain a minimum of one backup per Router regardless of the retention period, to ensure at least one restore point exists.

### Requirement 6: Backup Type Support

**User Story:** As an Admin, I want to choose between binary and text export formats, so that I can select the appropriate backup type for my recovery scenario.

#### Acceptance Criteria

1. THE Backup_Scheduler SHALL support creating Binary_Backup files via the RouterOS `/system/backup/save` API command.
2. THE Backup_Scheduler SHALL support creating Text_Export files via the RouterOS `/export` command.
3. WHEN creating a Binary_Backup, THE Backup_Scheduler SHALL encrypt the backup file at rest in Backup_Storage using application-level encryption.
4. WHEN the Admin configures scheduled backups, THE Backup_Scheduler SHALL allow selection of backup type (binary, text, or both).
5. THE Backup_Scheduler SHALL name backup files using the pattern: `{router_identity}_{date}_{time}.{extension}` where extension is `.backup` for binary or `.rsc` for text exports.

### Requirement 7: Restore Guidance

**User Story:** As an Admin, I want to see clear instructions on how to restore a backup, so that I can recover a router configuration without guessing the procedure.

#### Acceptance Criteria

1. WHEN the Admin views a backup detail page, THE Backup_Scheduler SHALL display restore instructions specific to the backup type (binary or text).
2. THE Backup_Scheduler SHALL display Binary_Backup restore instructions including: upload file to router via FTP/Winbox, then execute `/system/backup/load` command with the filename.
3. THE Backup_Scheduler SHALL display Text_Export restore instructions including: open terminal on router and execute `/import file-name={filename}` command.
4. THE Backup_Scheduler SHALL include a warning that restoring a binary backup will reboot the Router and overwrite the current configuration.

### Requirement 8: Security and Access Control

**User Story:** As an Admin, I want backup operations to be restricted to authorized users, so that sensitive router configurations are protected from unauthorized access.

#### Acceptance Criteria

1. THE Backup_Scheduler SHALL restrict all backup operations (create, download, delete, configure) to authenticated Admin users only.
2. THE Backup_Scheduler SHALL log all backup operations (create, download, delete) in the activity log with the Admin user identity and timestamp.
3. WHEN storing a Binary_Backup file, THE Backup_Scheduler SHALL encrypt the file at rest because binary backups contain router passwords.
4. THE Backup_Scheduler SHALL store backup files outside the public web root to prevent direct URL access.
