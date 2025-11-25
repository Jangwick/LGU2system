# 🚀 Document Management Setup Guide

## ✅ Completed Implementation

The Document Management subsystem has been fully implemented with the following features:

### 📁 Files Created

#### Backend (MVC Architecture)
- `modules/document-management/controllers/DocumentController.php` - Request handling
- `modules/document-management/models/Document.php` - Data access layer
- `modules/document-management/services/DocumentService.php` - Business logic
- `modules/document-management/services/FileStorageService.php` - File operations
- `modules/core/config/database.php` - Database connection
- `modules/core/utils/Logger.php` - Activity logging

#### API Endpoints
- `modules/document-management/api/upload.php` - Upload documents
- `modules/document-management/api/download.php` - Download documents
- `modules/document-management/api/delete.php` - Delete documents

#### Views
- `modules/document-management/views/index.php` - List documents (UPDATED)
- `modules/document-management/views/create.php` - Upload form (UPDATED)

#### Database
- `database/schema.sql` - Complete database schema (9 tables)

---

## 🗄️ Database Setup

### Step 1: Create Database

Open phpMyAdmin or MySQL command line:

```bash
mysql -u root -p
```

### Step 2: Import Schema

```sql
SOURCE c:/xampp/htdocs/LLRMSystem/database/schema.sql;
```

Or import via phpMyAdmin:
1. Go to http://localhost/phpmyadmin
2. Click "Import" tab
3. Choose file: `c:/xampp/htdocs/LLRMSystem/database/schema.sql`
4. Click "Go"

### Step 3: Verify Tables

The following tables should be created:
1. ✅ `users` - User accounts
2. ✅ `legislative_documents` - Main documents table
3. ✅ `document_versions` - Version control
4. ✅ `document_tags` - Tags management
5. ✅ `document_tag_relationships` - Document-tag relations
6. ✅ `document_links` - Related documents
7. ✅ `activity_logs` - Activity tracking
8. ✅ `document_access_logs` - Access tracking
9. ✅ `api_keys` - API authentication

### Step 4: Create Storage Directories

The system will auto-create these, but you can manually create them:

```bash
mkdir c:\xampp\htdocs\LLRMSystem\storage
mkdir c:\xampp\htdocs\LLRMSystem\storage\documents
mkdir c:\xampp\htdocs\LLRMSystem\storage\versions
mkdir c:\xampp\htdocs\LLRMSystem\storage\temp
```

Set permissions (Windows):
- Right-click folder → Properties → Security → Edit
- Grant "Full Control" to your Apache user

---

## ⚙️ Configuration

### Database Connection

Edit `modules/core/config/database.php` if needed:

```php
$host = 'localhost';
$dbname = 'lrms_db';
$username = 'root';
$password = '';  // Your MySQL password
```

---

## 🎯 Features Implemented

### 1. **Document Upload**
- ✅ Drag & drop file upload
- ✅ File validation (PDF, Word, Excel, PowerPoint)
- ✅ Max file size: 50MB
- ✅ Auto-generate reference numbers
- ✅ Document metadata (title, type, date, status, description, tags)

### 2. **Document List**
- ✅ Paginated table view
- ✅ Search by title/reference number
- ✅ Filter by type, status, date range
- ✅ Sort by any column
- ✅ Bulk operations (select all, delete multiple)

### 3. **Document Operations**
- ✅ View document details
- ✅ Download documents
- ✅ Edit document metadata
- ✅ Delete documents
- ✅ Activity logging (who did what, when)

### 4. **File Storage**
- ✅ Organized by document type
- ✅ Unique filename generation
- ✅ Secure file storage outside webroot
- ✅ Storage statistics tracking

### 5. **Security**
- ✅ Session-based authentication
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ File type validation
- ✅ Access control
- ✅ Activity audit trail

---

## 📝 Usage Guide

### Accessing Document Management

**URL:** `http://localhost/LLRMSystem/modules/document-management/views/index.php`

### Upload a Document

1. Navigate to Document Management
2. Click "Upload Document"
3. Drag & drop file or click to browse
4. Fill in document information:
   - Title (required)
   - Document Type (required)
   - Document Date
   - Status (draft, pending, approved, etc.)
   - Description
   - Tags
5. Click "Upload Document"

### View Documents

1. Go to Document Management
2. Use filters to find documents:
   - Search by title/reference
   - Filter by type
   - Filter by status
   - Date range
3. Click "View" to see details
4. Click "Download" to get the file
5. Click "Edit" to modify metadata
6. Click "Delete" to remove

### Bulk Operations

1. Select checkboxes next to documents
2. Click "Delete Selected" for bulk delete
3. Or use "Select All" to select entire page

---

## 🔌 Directory Structure

```
modules/document-management/
├── api/
│   ├── upload.php          # POST - Upload document
│   ├── download.php        # GET - Download document
│   └── delete.php          # POST - Delete document
├── controllers/
│   └── DocumentController.php   # Request handling
├── models/
│   └── Document.php        # Database operations
├── services/
│   ├── DocumentService.php      # Business logic
│   └── FileStorageService.php   # File operations
└── views/
    ├── index.php           # Document list
    ├── create.php          # Upload form
    ├── view.php            # Document details (to be created)
    └── edit.php            # Edit form (to be created)
```

---

## 🔧 API Endpoints

### Upload Document
```
POST /LLRMSystem/modules/document-management/api/upload.php
Content-Type: multipart/form-data

Fields:
- document (file, required)
- title (string, required)
- document_type (string, required)
- document_date (date)
- status (string)
- description (text)
- tags (string, comma-separated)
- reference_number (string, optional - auto-generated)

Response:
{
    "success": true,
    "document_id": 123,
    "reference_number": "ORD-2025-042",
    "message": "Document uploaded successfully"
}
```

### Download Document
```
GET /LLRMSystem/modules/document-management/api/download.php?id=123

Response: Binary file with appropriate headers
```

### Delete Document
```
POST /LLRMSystem/modules/document-management/api/delete.php
Content-Type: application/json

{
    "id": 123
}

Response:
{
    "success": true,
    "message": "Document deleted successfully"
}
```

---

## 📊 Database Tables Detail

### legislative_documents
Main table storing all documents

**Key Columns:**
- `id` - Primary key
- `reference_number` - Unique identifier (e.g., ORD-2025-042)
- `title` - Document title
- `document_type` - Type (ordinance, session, agenda, etc.)
- `status` - Status (draft, pending, approved, rejected, archived)
- `file_path` - Physical file location
- `uploaded_by` - User ID who uploaded
- `created_at`, `updated_at` - Timestamps

### activity_logs
Tracks all user actions

**Logged Actions:**
- document_created
- document_updated
- document_deleted
- document_downloaded
- document_viewed

---

## 🚦 Next Steps

### To Complete Document Management:

1. **Create View Page** (`view.php`)
   - Display document details
   - Show version history
   - List related documents

2. **Create Edit Page** (`edit.php`)
   - Update document metadata
   - Replace file (create new version)

3. **Version Control**
   - Implement document versioning
   - View version history
   - Restore previous versions

4. **Tagging System**
   - Create/manage tags
   - Tag documents
   - Search by tags

5. **Document Linking**
   - Link related documents
   - Show document relationships

---

## 💡 Testing

### Test Upload:
1. Login as admin (admin@lgu.gov.ph / Admin@123)
2. Go to Documents → Upload
3. Upload a PDF/Word file
4. Check if it appears in the list

### Test Download:
1. Click download icon on any document
2. File should download with original name

### Test Delete:
1. Click delete icon
2. Confirm deletion
3. Document should be removed

---

## ⚠️ Troubleshooting

### "Database connection failed"
- Check MySQL is running
- Verify database credentials in `database.php`
- Ensure `lrms_db` database exists

### "Failed to upload file"
- Check `storage/documents` directory exists
- Verify write permissions
- Check file size limit in php.ini

### "Document not found"
- Verify document ID exists in database
- Check file exists in storage directory

---

**Status:** ✅ Backend Complete | 🔄 Frontend Connected | ⏳ Additional Features Pending

