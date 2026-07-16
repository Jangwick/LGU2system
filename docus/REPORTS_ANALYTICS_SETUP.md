# Reports & Analytics Module - Implementation Documentation

## Overview
The Reports & Analytics module provides comprehensive statistical analysis and data visualization for the LRMS system. It offers insights into document management, user activity, storage utilization, and system trends.

## Features Implemented

### 1. Dashboard Statistics (7 Key Metrics)
- **Total Documents**: Overall count of all documents in the system
- **Approved Documents**: Count of documents with 'approved' status
- **Pending Documents**: Documents awaiting approval
- **New Documents (30 days)**: Recent upload statistics
- **Active Users**: Users who have logged in or performed actions
- **Activities (24 hours)**: Recent system activity count
- **Total Storage**: Cumulative file storage usage

### 2. Visual Analytics (6 Charts)

#### A. Documents by Type (Doughnut Chart)
- Visual distribution of documents across different types
- Color-coded segments for easy identification
- Interactive legend with click-to-filter

#### B. Documents by Status (Pie Chart)
- Status breakdown: Approved, Pending, Draft, Rejected, Archived
- Percentage distribution display
- Hover tooltips with exact counts

#### C. Upload Timeline (Line Chart)
- 12-month trend analysis of document uploads
- Filled area chart with smooth curves
- Month-by-month growth visualization
- Zero-baseline for accurate comparison

#### D. Activity by Action (Bar Chart)
- 30-day activity breakdown by action type
- Actions: Create, Update, Delete, Login, View, Download
- Vertical bar chart with counts

#### E. Documents by Department (Horizontal Bar Chart)
- Department-wise document distribution
- Sorted by document count
- Easy comparison across departments

#### F. Storage by Type Chart
- Storage utilization breakdown by document type
- Helps identify space-consuming document categories

### 3. Data Tables

#### Top Uploaders Table
- Lists top 5 most active uploaders
- Shows user avatar, full name, department
- Displays total document count per user
- Sortable and filterable

#### Storage Usage by Type Table
- Document type breakdown
- File size calculations in KB/MB/GB
- Helps identify storage optimization opportunities

#### Recent Activities Table
- Last 10 system activities
- Columns: Timestamp, User, Action, Description
- Color-coded action badges:
  - Green: Create actions
  - Blue: Update actions
  - Red: Delete actions
  - Indigo: Login actions
  - Gray: Other actions
- Real-time activity monitoring

### 4. Advanced Reporting

#### User Activity Report
- Comprehensive user activity analysis
- Date range filtering (start date, end date)
- Shows: User name, action count, last activity
- Exportable to CSV

#### Document Access Report
- Top 50 most accessed documents
- Tracks view and download counts
- Shows document title, type, access count
- Helps identify popular content
- Exportable to CSV

#### Top Uploaders Report
- Extended list of up to 50 top contributors
- Includes user details and upload counts
- Department-wise breakdown
- Exportable to CSV

### 5. Export Functionality

#### Export Modal
- User-friendly modal interface
- Report type selection dropdown:
  - User Activity Report
  - Document Access Report
  - Top Uploaders Report
- Optional date range filters
- One-click CSV export

#### CSV Export Features
- Proper CSV formatting with headers
- UTF-8 encoding support
- Automatic file download
- Timestamped filenames
- Excel-compatible format

### 6. Additional Analytics

#### Monthly Growth Metrics
- 6-month growth analysis
- Documents created per month
- Active users per month
- Storage growth tracking
- Trend identification

#### Storage Analytics
- Total storage calculation
- Storage by document type
- Average file size per type
- Storage optimization insights

## File Structure

```
modules/reports-analytics/
├── controllers/
│   └── ReportController.php (250+ lines)
└── views/
    └── index.php (650+ lines)
```

## Controller Methods

### ReportController.php

1. **getDashboardStats()**: Returns 7 key system metrics
2. **getDocumentsByType()**: Groups documents by type with counts
3. **getDocumentsByStatus()**: Groups documents by status
4. **getDocumentsTimeline()**: 12-month upload trend data
5. **getTopUploaders($limit)**: Top document uploaders
6. **getActivityByAction()**: Activity breakdown by action type
7. **getRecentActivities($limit)**: Latest system activities
8. **getDocumentsByDepartment()**: Department-wise analysis
9. **getUserActivityReport($startDate, $endDate)**: User activity with filters
10. **getDocumentAccessReport($startDate, $endDate)**: Document access tracking
11. **exportToCSV($reportType, $data)**: Generic CSV export function
12. **getStorageByType()**: Storage analysis by type
13. **getMonthlyGrowth()**: 6-month growth statistics

## UI Components

### Header Section
- Page title: "Reports & Analytics"
- Subtitle: "Comprehensive insights and statistical analysis"
- Export Reports button (opens modal)
- Print button (print-friendly layout)

### Statistics Cards (4 Columns)
- Card 1: Total Documents (Blue icon)
- Card 2: Approved Documents (Green icon)
- Card 3: Active Users (Indigo icon)
- Card 4: Storage Used (Amber icon)
- Each card shows icon, label, and formatted value

### Charts Layout
- Row 1: Documents by Type + Documents by Status (2 columns)
- Row 2: Upload Timeline (full width)
- Row 3: Activity by Action + Documents by Department (2 columns)

### Tables Layout
- Row 1: Top Uploaders + Storage by Type (2 columns)
- Row 2: Recent Activities (full width)

### Export Modal
- Centered modal overlay
- Report type dropdown
- Start date picker (optional)
- End date picker (optional)
- Cancel button
- Export CSV button

## Design System

### Tailwind CSS Classes Used
- **Cards**: `bg-white rounded-xl shadow-sm border border-gray-200 p-6`
- **Headers**: `text-lg font-semibold text-gray-900 mb-4`
- **Buttons**: `btn-success`, `btn-outline`, `btn-primary`, `btn-secondary`
- **Icons**: Bootstrap Icons (bi-*)
- **Tables**: `min-w-full divide-y divide-gray-200`
- **Badges**: Color-coded with Tailwind utility classes

### Chart.js Configuration
- **Color Palette**: 8 predefined colors (blue, green, red, yellow, purple, indigo, pink, orange)
- **Responsive**: All charts adapt to container width
- **Interactive**: Hover tooltips, clickable legends
- **Smooth**: Tension curves for line charts

## Access Control

### Permission Requirements
- **Minimum Role**: Officer
- **Recommended**: Administrator
- **Permission**: `view_reports` (if using granular permissions)

### Role Normalization
- Handles both 'administrator' and 'admin' roles
- Case-insensitive role checking
- Session-based authentication

## Integration Points

### Database Tables Used
1. **documents**: Document statistics and counts
2. **users**: User information and activity
3. **activity_logs**: System activity tracking
4. **document_versions**: Version control data

### Required Database Columns
- documents: id, title, document_type, status, uploaded_by, upload_date, file_size
- users: id, full_name, email, department, is_active
- activity_logs: id, user_id, action, description, created_at

## Performance Optimizations

1. **Efficient Queries**: Uses GROUP BY and aggregate functions
2. **Limited Results**: Reasonable limits on data fetching (Top 50, Last 10)
3. **Chart Caching**: Static chart configurations
4. **Lazy Loading**: Charts render after page load

## Browser Compatibility

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support
- Mobile: Responsive charts and tables

## Print Functionality

### Print-Friendly Features
- Optimized layout for paper
- Removes interactive elements (buttons, modals)
- Preserves charts and tables
- Page break handling

### Print CSS (Auto-applied)
- Hides navigation sidebar
- Expands content to full width
- Maintains chart visibility
- Clean table formatting

## Usage Instructions

### Accessing Reports
1. Login as Administrator or Officer
2. Navigate to sidebar → Management → Reports & Analytics
3. View dashboard statistics and charts automatically

### Exporting Reports
1. Click "Export Reports" button
2. Select report type from dropdown
3. (Optional) Set start/end date filters
4. Click "Export CSV"
5. File downloads automatically

### Printing Reports
1. Open Reports & Analytics page
2. Click "Print" button
3. Browser print dialog appears
4. Select printer or "Save as PDF"
5. Confirm print

## Troubleshooting

### Charts Not Displaying
**Issue**: Blank chart areas
**Solution**: 
- Check browser console for errors
- Verify Chart.js CDN is loaded
- Ensure data arrays are not empty

### Export Not Working
**Issue**: CSV file not downloading
**Solution**:
- Check PHP error logs
- Verify write permissions
- Ensure no output before headers
- Check date range validity

### Permission Denied
**Issue**: 403 Access Denied error
**Solution**:
- Verify user role is officer+ or admin
- Check session variables
- Run database migration 002 if needed

### Slow Loading
**Issue**: Page takes long to load
**Solution**:
- Database indexes needed on activity_logs
- Reduce chart data points
- Implement caching for dashboard stats

## Future Enhancements

### Phase 1 (Near-term)
- [ ] Real-time chart updates via WebSockets
- [ ] Drill-down functionality on charts
- [ ] Custom date range picker
- [ ] Report scheduling (daily/weekly emails)
- [ ] PDF export with charts

### Phase 2 (Mid-term)
- [ ] Predictive analytics using ML
- [ ] Comparative analysis (month-over-month)
- [ ] Department-specific dashboards
- [ ] Custom report builder
- [ ] Data export to Excel with formatting

### Phase 3 (Long-term)
- [ ] Interactive dashboard widgets
- [ ] Drag-and-drop dashboard customization
- [ ] API endpoints for external reporting tools
- [ ] Mobile app integration
- [ ] Advanced filtering and segmentation

## Testing Checklist

### Functional Testing
- [x] Dashboard loads without errors
- [x] All 7 statistics display correctly
- [x] Charts render with accurate data
- [x] Tables populate with records
- [x] Export modal opens/closes
- [x] CSV export downloads properly
- [x] Print function works
- [x] Date filters apply correctly

### Data Validation
- [x] Statistics match database counts
- [x] Chart totals sum correctly
- [x] Top uploaders sorted accurately
- [x] Recent activities in correct order
- [x] Storage calculations accurate

### Security Testing
- [x] Permission checks prevent unauthorized access
- [x] SQL injection prevention in queries
- [x] XSS protection in output
- [x] CSV export sanitizes data

### Performance Testing
- [ ] Page loads in < 2 seconds
- [ ] Charts render in < 1 second
- [ ] Export generates in < 5 seconds
- [ ] Handles 10,000+ documents efficiently

## Conclusion

The Reports & Analytics module is now fully implemented with comprehensive data visualization, interactive charts, exportable reports, and print functionality. It provides administrators and officers with powerful insights into system usage, document trends, user activity, and storage utilization.

**Status**: ✅ **COMPLETE**
**Version**: 1.0.0
**Last Updated**: <?php echo date('Y-m-d'); ?>
