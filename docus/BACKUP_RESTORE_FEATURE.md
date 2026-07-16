# Backup and Restore Feature Implementation Plan

## 1. Overview
This feature allows administrators to perform selective backups and restorations of data based on **Subsystems** (e.g., Ordinances, Resolutions, Committee Records) or **Departments**. This provides flexibility in data management and ensures that large systems can be backed up in manageable increments.

## 2. Technical Approach

### 2.1 Backup Structure
Backup packages will be generated as a `.zip` file stored in `storage/backups/`. Each package will contain:
- `metadata.json`: Information about the backup (date, creator, source subsystem, system version).
- `registry.json`: JSON export of database records from relevant tables.
- `files/`: A directory containing all physical documents associated with the exported records.

### 2.2 Data Scope
Data will be filtered based on the `source_module` field in the `legislative_documents` table. 
The following subsystems are supported:
- `agenda`
- `committee`
- `ordinance`
- `research`
- `resolution`
- `session`

### 2.3 Tables to be Exported
For a selected subsystem, the system will export:
1. **`legislative_documents`**: Records where `source_module = ?`.
2. **`document_versions`**: All versions associated with the exported documents.
3. **`document_links`**: Relationships between the exported documents.
4. **`document_tags_relationships`**: Tag assignments for the documents.
5. **`activity_logs`**: Logs related to the specific documents and the subsystem.
6. **`notifications`**: Relevant notification history for that module.

## 3. Architecture

### 3.1 Backend Components
- **`BackupService.php`**: 
    - `createSubsystemBackup($moduleName)`: Handles record extraction and file aggregation.
    - `restoreBackup($zipPath)`: Validates manifest and performs database transactions to merge or overwrite data.
- **`FileArchiver.php`**: Utility to handle ZIP creation and extraction using PHP's `ZipArchive`.

### 3.2 Database Schema Update
A new table `system_backups` will be created to track backup history:
```sql
CREATE TABLE system_backups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    subsystem VARCHAR(50) NOT NULL,
    backup_date DATETIME NOT NULL,
    created_by INT,
    file_size BIGINT,
    status ENUM('success', 'failed') DEFAULT 'success',
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

## 4. Implementation Steps

### Phase 1: Infrastructure (In Progress)
- [ ] Create `storage/backups/` directory with `.htaccess` protection.
- [ ] Implement `BackupService` with `ZipArchive` integration.
- [ ] Create `system_backups` tracking table.

### Phase 2: Export Logic
- [ ] Implement SQL query builder for selective data extraction.
- [ ] Develop file collector that maps `file_path` from database to `storage/documents/`.
- [ ] Generate JSON serialization for database records.

### Phase 3: Restore Logic
- [ ] Implement ZIP extraction and validation.
- [ ] Develop conflict resolution (e.g., skip existing, overwrite, or create duplicate).
- [ ] Implement database transaction wrapper for atomic restoration.

### Phase 4: User Interface
- [ ] Create Backup Management Dashboard in `modules/system/views/backup.php`.
- [ ] Add "Backup Now" buttons for each subsystem.
- [ ] Add "Restore" upload functionality with progress indicators.

## 5. Security Considerations
- **Access Control:** Only users with `administrator` role can access the backup module.
- **Encryption:** (Optional) Add password protection to the ZIP files.
- **Integrity Check:** Use SHA-256 hashes in `metadata.json` to verify files haven't been tampered with.

## 6. Directory Structure
```
modules/
└── backup/
    ├── controllers/
    │   └── BackupController.php
    ├── models/
    │   └── Backup.php
    ├── services/
    │   └── BackupService.php
    └── views/
        ├── index.php
        └── history.php
```
