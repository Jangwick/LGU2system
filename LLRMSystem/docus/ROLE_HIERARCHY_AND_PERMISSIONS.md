# Role Hierarchy and Permissions

## Account Hierarchy

The system implements a 4-tier role hierarchy with increasing privileges:

```
Viewer (Level 1) → Staff (Level 2) → Officer (Level 3) → Administrator (Level 4)
```

---

## Role Definitions

### 1. **Viewer** (Read-Only Access)
**Account:** `viewer@lgu.gov.ph` / `password`

**Purpose:** Public or limited access users who need to view approved documents only.

**Permissions:**
- ✅ View Dashboard (limited statistics)
- ✅ View **Approved**, **Archived**, and **Rejected** documents
- ✅ Download approved documents
- ✅ Search documents (approved/archived/rejected only)
- ❌ Cannot upload documents
- ❌ Cannot edit any documents
- ❌ Cannot delete documents
- ❌ Cannot access pending/draft documents
- ❌ Cannot access Reports & Analytics
- ❌ Cannot access User Management
- ❌ Cannot access Audit Logs
- ❌ Cannot access Integration Modules

**Dashboard Access:**
- Can view basic document statistics
- Cannot see user statistics
- Cannot see activity logs
- Cannot see recent uploads

---

### 2. **Staff** (Document Creator)
**Account:** `staff@lgu.gov.ph` / `password`

**Purpose:** Regular staff members who create and manage their own documents.

**Permissions:**
- ✅ View Dashboard (enhanced statistics)
- ✅ View **Approved**, **Archived**, and **Pending** documents
- ✅ **Upload new documents**
- ✅ **Edit their own documents** only
- ✅ **Delete their own documents** only
- ✅ Download documents
- ✅ Search all visible documents
- ✅ Create tags for documents
- ✅ Create document versions
- ✅ Create document links
- ❌ Cannot edit other users' documents
- ❌ Cannot delete other users' documents
- ❌ Cannot approve/reject documents
- ❌ Cannot access Reports & Analytics
- ❌ Cannot access User Management
- ❌ Cannot access Audit Logs
- ❌ Cannot access Integration Modules

**Dashboard Access:**
- Can see their own upload statistics
- Can see documents they created
- Can see their recent activity
- Cannot see other users' activity

---

### 3. **Officer** (Document Manager)
**Account:** `officer@lgu.gov.ph` / `password`

**Purpose:** Legislative officers who oversee and manage all documents.

**Permissions:**
- ✅ View Dashboard (full statistics)
- ✅ View **ALL documents** (including drafts)
- ✅ Upload new documents
- ✅ **Edit ANY document**
- ✅ **Delete ANY document**
- ✅ **Approve/Reject documents**
- ✅ Download documents
- ✅ Advanced search
- ✅ Create and edit tags
- ✅ Manage document versions
- ✅ **Revert to previous versions**
- ✅ Manage document links
- ✅ **Access Reports & Analytics**
- ✅ Generate reports
- ✅ Export data (CSV, Excel)
- ✅ **Access Integration Modules**
- ❌ Cannot manage users
- ❌ Cannot access Audit Logs
- ❌ Cannot delete tags

**Dashboard Access:**
- Full document statistics
- All users' activity
- Recent uploads from all users
- Document approval queue
- System-wide analytics

**Reports & Analytics:**
- Generate custom reports
- View document trends
- Export statistics
- Schedule automated reports

**Integration Modules:**
- Access ordinances module
- Access sessions module
- Access agendas module
- Access committees module
- Access voting records
- Access public hearings
- Access archives
- Access consultations
- Access research module

---

### 4. **Administrator** (Full System Access)
**Account:** `admin@lgu.gov.ph` / `admin123`

**Purpose:** System administrators with complete control over all features.

**Permissions:**
- ✅ **ALL Officer permissions**
- ✅ **User Management**
  - Create new users
  - Edit user accounts
  - Delete users
  - Change user roles
  - Suspend/Activate accounts
- ✅ **Audit Logs**
  - View all system activity
  - Track user actions
  - Monitor login attempts
  - Export audit reports
- ✅ **Advanced Administration**
  - Delete tags
  - Restore deleted documents
  - Manage API keys
  - System configuration
- ✅ **Full Reports & Analytics**
  - Advanced analytics
  - User activity reports
  - System health monitoring
- ✅ **Integration Module Management**
  - Configure integration settings
  - Manage API connections

**Dashboard Access:**
- Complete system overview
- All user statistics
- System health metrics
- Security alerts
- Full activity timeline

**Exclusive Features:**
- User account creation/deletion
- Role assignment
- Audit log access
- System configuration
- API key management
- Document restoration
- Tag deletion

---

## Permission Matrix

| Feature | Viewer | Staff | Officer | Admin |
|---------|--------|-------|---------|-------|
| **Documents** |
| View Approved Docs | ✅ | ✅ | ✅ | ✅ |
| View Pending Docs | ❌ | ✅ | ✅ | ✅ |
| View Draft Docs | ❌ | Own Only | ✅ | ✅ |
| Upload Documents | ❌ | ✅ | ✅ | ✅ |
| Edit Own Docs | ❌ | ✅ | ✅ | ✅ |
| Edit Any Docs | ❌ | ❌ | ✅ | ✅ |
| Delete Own Docs | ❌ | ✅ | ✅ | ✅ |
| Delete Any Docs | ❌ | ❌ | ✅ | ✅ |
| Download Docs | ✅ | ✅ | ✅ | ✅ |
| Restore Deleted | ❌ | ❌ | ❌ | ✅ |
| **Tags & Metadata** |
| Create Tags | ❌ | ✅ | ✅ | ✅ |
| Edit Tags | ❌ | ❌ | ✅ | ✅ |
| Delete Tags | ❌ | ❌ | ❌ | ✅ |
| **Versions** |
| Create Versions | ❌ | ✅ | ✅ | ✅ |
| Revert Versions | ❌ | ❌ | ✅ | ✅ |
| **Links** |
| Create Links | ❌ | ✅ | ✅ | ✅ |
| Delete Links | ❌ | ❌ | ✅ | ✅ |
| **Reports** |
| View Reports | ❌ | ❌ | ✅ | ✅ |
| Generate Reports | ❌ | ❌ | ✅ | ✅ |
| Export Data | ❌ | ❌ | ✅ | ✅ |
| **Users** |
| View Users | ❌ | ❌ | ❌ | ✅ |
| Create Users | ❌ | ❌ | ❌ | ✅ |
| Edit Users | ❌ | ❌ | ❌ | ✅ |
| Delete Users | ❌ | ❌ | ❌ | ✅ |
| **Audit** |
| View Audit Logs | ❌ | ❌ | ❌ | ✅ |
| Export Audit Logs | ❌ | ❌ | ❌ | ✅ |
| **Integration** |
| Access Modules | ❌ | ❌ | ✅ | ✅ |
| Configure Modules | ❌ | ❌ | ❌ | ✅ |

---

## Document Access Rules

### Document Status Visibility

| Status | Viewer | Staff | Officer | Admin |
|--------|--------|-------|---------|-------|
| Draft | ❌ | Own Only | ✅ All | ✅ All |
| Pending | ❌ | ✅ All | ✅ All | ✅ All |
| Approved | ✅ | ✅ | ✅ | ✅ |
| Rejected | ✅ | Own Only | ✅ All | ✅ All |
| Archived | ✅ | ✅ | ✅ | ✅ |
| Deleted | ❌ | ❌ | ❌ | ✅ View Only |

### Action Restrictions

**Viewers:**
- Can view and download approved, archived, and rejected documents
- Cannot see drafts or pending documents
- No modification capabilities

**Staff:**
- Can view approved, pending, and archived documents
- Can view their own drafts and rejected documents
- Can edit/delete ONLY their own documents
- Cannot approve/reject documents

**Officers:**
- Can view ALL documents regardless of status
- Can edit/delete ANY document
- Can approve/reject documents
- Cannot restore permanently deleted documents

**Administrators:**
- Complete access to all documents
- Can restore deleted documents
- Can modify any document metadata
- Can reassign document ownership

---

## Navigation Menu Access

### Visible Menu Items by Role

**Viewer:**
- Dashboard
- All Documents
- Advanced Search

**Staff:**
- Dashboard
- All Documents
- **Upload Document**
- Advanced Search

**Officer:**
- Dashboard
- All Documents
- Upload Document
- Advanced Search
- **Reports & Analytics**
- **Integration Modules**

**Administrator:**
- Dashboard
- All Documents
- Upload Document
- Advanced Search
- Reports & Analytics
- Integration Modules
- **User Management**
- **Audit Logs**

---

## Security Implementation

### Permission Checks
- All controllers check permissions before executing actions
- API endpoints validate user roles
- Middleware enforces access control
- Database queries filter by user permissions

### Session Management
- User role stored in session
- Role validated on each request
- Permissions cached per session
- Automatic logout on role change

### Audit Trail
- All actions logged with user information
- Permission violations logged
- Failed access attempts tracked
- Administrator-only log access

---

## Testing Accounts

```
Administrator:
Email: admin@lgu.gov.ph
Password: admin123
Access: Full system control

Officer:
Email: officer@lgu.gov.ph
Password: password
Access: Document management + Reports + Integration

Staff:
Email: staff@lgu.gov.ph
Password: password
Access: Upload + Edit own documents

Viewer:
Email: viewer@lgu.gov.ph
Password: password
Access: View approved documents only
```

---

## Implementation Files

**Permission Middleware:**
- `modules/core/middleware/PermissionMiddleware.php` - Central permission management

**Sidebar Navigation:**
- `modules/core/layouts/sidebar.php` - Role-based menu visibility

**Document Management:**
- `modules/document-management/views/index.php` - Action button visibility
- `modules/document-management/controllers/DocumentController.php` - Permission enforcement

**User Management:**
- `modules/user-management/controllers/UserController.php` - Admin-only access

**Audit Logs:**
- `modules/audit/controllers/AuditController.php` - Admin-only access

**Reports & Analytics:**
- `modules/reports-analytics/controllers/ReportController.php` - Officer+ access

---

## Upgrade Path

Users can be promoted through the hierarchy:
1. Viewer → Staff (gain document creation)
2. Staff → Officer (gain approval + reports)
3. Officer → Administrator (gain user management + audit)

Only administrators can change user roles.
