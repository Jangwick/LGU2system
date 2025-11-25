# Export Functionality Implementation

## Overview
The export feature allows users to export both document metadata lists (CSV/Excel) and actual document files (ZIP) with role-based permissions.

## Features Implemented

### 1. **Export API Endpoint**
**File:** `modules/document-management/api/export.php`

**Functionality:**
- ✅ Export document list to CSV or Excel format
- ✅ **Export actual document files as ZIP archive**
- ✅ Respects role-based permissions
- ✅ Supports selective export (selected documents)
- ✅ Supports bulk export (all filtered documents)
- ✅ UTF-8 BOM support for proper character encoding

**Export Types:**

#### **1. Document List Export** (`?type=list`)
Export metadata information in spreadsheet format.

**CSV Format** (`?type=list&format=csv`)
- Plain text format
- Compatible with Excel, Google Sheets
- Filename: `documents_export_YYYY-MM-DD_HHMMSS.csv`

**Excel Format** (`?type=list&format=excel`)
- HTML-based Excel format (.xls)
- Styled headers and alternating rows
- Filename: `documents_export_YYYY-MM-DD_HHMMSS.xls`

**Exported Columns:**
1. Reference Number
2. Title
3. Document Type
4. Status
5. Document Date
6. File Name
7. File Size
8. Uploaded By
9. Created At
10. Updated At

#### **2. Document Files Export** (`?type=files`)
Export actual uploaded files (PDF, Word, Excel, etc.) in ZIP format.

**Features:**
- Downloads original uploaded files
- Packages files in ZIP archive
- Preserves file types (PDF, DOCX, XLSX, PPTX, etc.)
- Files named with reference number prefix for easy identification
- Example: `ORD-2025-001_Budget_Ordinance.pdf`

**Parameters:**
- `ids` - Comma-separated document IDs for selective export
- Without `ids` - Exports all documents based on current filters

### 2. **Export Dropdown UI**
**File:** `modules/document-management/views/index.php`

**Menu Options:**

**Export List:**
- 📊 Export List as CSV - Metadata in CSV format
- 📊 Export List as Excel - Metadata in Excel format

**Export Files:**
- 📦 Export Selected Files (ZIP) - Download checked documents
- 📦 Export All Files (ZIP) - Download all visible documents

**Features:**
- ✅ Dropdown menu with categorized options
- ✅ Visual icons for each export type
- ✅ Checkbox selection for bulk operations
- ✅ "Select All" functionality
- ✅ Selected count display
- ✅ Auto-closes on click outside

### 3. **JavaScript Functionality**
**File:** `modules/document-management/views/index.php` (inline script)

**Functions:**
- `toggleSelectAll()` - Select/deselect all document checkboxes
- `updateSelectedCount()` - Shows "X selected of Y documents"
- `getSelectedDocumentIds()` - Returns array of selected document IDs
- `exportSelectedFiles()` - Downloads selected documents as ZIP
- `exportAllFiles()` - Downloads all filtered documents as ZIP
- `toggleExportMenu()` - Shows/hides export dropdown
- Click-outside detection to auto-close menu

**Workflow:**
1. User checks documents they want to export
2. Selected count updates dynamically
3. Click "Export Selected Files" → ZIP downloads with only checked files
4. OR click "Export All Files" → ZIP downloads with all visible files

## Role-Based Export Behavior

### **Viewer** (viewer@lgu.gov.ph)
- ✅ Can export document list
- Only exports **approved**, **archived**, and **rejected** documents
- Cannot export pending or draft documents
- Limited to 10,000 documents per export

### **Staff** (staff@lgu.gov.ph)
- ✅ Can export document list
- Exports approved, archived, pending, and rejected documents
- Can see their own drafts in export
- Limited to 10,000 documents per export

### **Officer** (officer@lgu.gov.ph)
- ✅ Can export document list
- Exports **ALL** documents including drafts
- Full access to all document statuses
- Limited to 10,000 documents per export

### **Administrator** (admin@lgu.gov.ph)
- ✅ Can export document list
- Exports **ALL** documents including deleted (if modified)
- Full unrestricted access
- Limited to 10,000 documents per export

## Security Features

1. **Authentication Required:**
   - Redirects to login if not authenticated
   - Session validation on every export

2. **Role-Based Filtering:**
   - Automatically filters documents based on user role
   - Uses same permission logic as document list view
   - No manual role checking needed in export code

3. **Data Sanitization:**
   - All output is HTML-escaped to prevent XSS
   - File size formatting to prevent data corruption
   - UTF-8 encoding for international characters

## Usage

### **Export Document List (Metadata):**
1. Navigate to Document Management
2. (Optional) Apply filters
3. Click "Export" → "Export List as CSV" or "Export List as Excel"
4. Spreadsheet downloads with document information

### **Export Selected Document Files:**
1. Navigate to Document Management
2. Check boxes next to documents you want to download
3. Click "Export" → "Export Selected Files (ZIP)"
4. ZIP file downloads containing actual PDF/Word/Excel files

### **Export All Document Files:**
1. Navigate to Document Management
2. (Optional) Apply filters to narrow selection
3. Click "Export" → "Export All Files (ZIP)"
4. Confirm the action
5. ZIP file downloads with all visible documents

### **For Developers:**
```php
// Export document list
GET /api/export.php?type=list&format=csv
GET /api/export.php?type=list&format=excel

// Export specific files
GET /api/export.php?type=files&ids=1,5,12,34

// Export all filtered files
GET /api/export.php?type=files&type=ordinance&status=approved
```

## File Structure

```
modules/document-management/
├── api/
│   └── export.php          (NEW - Export API endpoint)
├── views/
│   └── index.php           (MODIFIED - Added export dropdown)
```

## Technical Details

### CSV Export Process:
1. Set headers for CSV download
2. Add UTF-8 BOM for proper encoding
3. Write header row with column names
4. Loop through documents and write data rows
5. Format file size for readability
6. Close output stream and exit

### Excel Export Process:
1. Set headers for Excel download
2. Add UTF-8 BOM
3. Generate HTML table with styling
4. Apply CSS for professional appearance
5. Write data rows with proper escaping
6. Close HTML structure and exit

### Performance Considerations:
- **Export Limit:** 10,000 documents maximum
- **Memory Efficient:** Uses `fopen('php://output')` for streaming
- **No Temporary Files:** Direct output to browser
- **Fast Processing:** Minimal memory footprint

## Browser Compatibility
- ✅ Chrome/Edge (Latest)
- ✅ Firefox (Latest)
- ✅ Safari (Latest)
- ✅ Opera (Latest)
- ✅ Mobile browsers

## Future Enhancements (Optional)

1. **PDF Export:**
   - Add PDF format using TCPDF or FPDF library
   - Formatted document report with logo and headers

2. **Advanced Excel Features:**
   - True XLSX format using PhpSpreadsheet
   - Formulas, charts, and pivot tables
   - Custom column widths and formatting

3. **Scheduled Exports:**
   - Automated weekly/monthly export emails
   - Configurable export schedules per user

4. **Custom Column Selection:**
   - Allow users to choose which columns to export
   - Save export preferences per user

5. **Export History:**
   - Track export activities in audit logs
   - Download previous exports

## Testing Checklist

### ✅ CSV Export:
- [x] Downloads file with correct filename
- [x] File opens in Excel/Google Sheets
- [x] UTF-8 characters display correctly
- [x] All columns present and formatted
- [x] Role-based filtering works

### ✅ Excel Export:
- [x] Downloads as .xls file
- [x] Opens in Microsoft Excel
- [x] Styling applied correctly
- [x] UTF-8 characters display correctly
- [x] All columns present and formatted
- [x] Role-based filtering works

### ✅ UI/UX:
- [x] Dropdown menu opens on click
- [x] Dropdown closes when clicking outside
- [x] Icons display correctly
- [x] Responsive on mobile devices
- [x] Accessible with keyboard navigation

### ✅ Permissions:
- [x] Viewer sees only approved/archived/rejected documents
- [x] Staff sees approved/archived/pending/rejected + own drafts
- [x] Officer sees all documents
- [x] Administrator sees all documents

## Error Handling

**No Documents Found:**
- Exports empty file with headers only
- User can see the structure even with no data

**Invalid Format:**
- Returns JSON error: `{"error": "Invalid export format"}`
- Defaults to CSV if format parameter missing

**Authentication Error:**
- Redirects to login page
- Session timeout handled gracefully

## Conclusion

The export functionality is now fully operational with professional-grade features:
- ✅ Multiple format support (CSV, Excel)
- ✅ Role-based security enforcement
- ✅ UTF-8 character support
- ✅ Professional UI with dropdown menu
- ✅ Automatic filename generation
- ✅ Memory-efficient streaming
- ✅ Proper error handling

**Status:** ✅ COMPLETE AND TESTED
**Date Implemented:** November 22, 2025
