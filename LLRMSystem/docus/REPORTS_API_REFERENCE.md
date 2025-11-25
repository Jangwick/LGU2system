# Reports & Analytics API Reference

## Controller Methods

### Class: `ReportController`
**Location**: `modules/reports-analytics/controllers/ReportController.php`

---

## Dashboard Methods

### 1. getDashboardStats()
**Purpose**: Get key system statistics for dashboard cards  
**Returns**: Array with 7 metrics  
**Permission**: Officer+

```php
$stats = $controller->getDashboardStats();
```

**Return Structure**:
```php
[
    'total_documents' => 1234,
    'approved_documents' => 980,
    'pending_documents' => 124,
    'new_documents_30_days' => 45,
    'active_users' => 28,
    'activities_24_hours' => 156,
    'total_storage' => 5242880000  // bytes
]
```

---

## Document Analytics

### 2. getDocumentsByType()
**Purpose**: Group documents by type with counts  
**Returns**: Array of type distribution  
**Use Case**: Pie/Doughnut charts

```php
$data = $controller->getDocumentsByType();
```

**Return Structure**:
```php
[
    ['document_type' => 'ordinance', 'count' => 450],
    ['document_type' => 'resolution', 'count' => 320],
    ['document_type' => 'agenda', 'count' => 180],
    // ...
]
```

---

### 3. getDocumentsByStatus()
**Purpose**: Group documents by status  
**Returns**: Array of status distribution  
**Use Case**: Status overview charts

```php
$data = $controller->getDocumentsByStatus();
```

**Return Structure**:
```php
[
    ['status' => 'approved', 'count' => 980],
    ['status' => 'pending', 'count' => 124],
    ['status' => 'draft', 'count' => 89],
    ['status' => 'rejected', 'count' => 23],
    ['status' => 'archived', 'count' => 18]
]
```

---

### 4. getDocumentsTimeline()
**Purpose**: Get 12-month upload trend  
**Returns**: Array of monthly counts  
**Use Case**: Line chart showing upload patterns

```php
$data = $controller->getDocumentsTimeline();
```

**Return Structure**:
```php
[
    ['month' => '2024-01', 'count' => 45],
    ['month' => '2024-02', 'count' => 52],
    ['month' => '2024-03', 'count' => 38],
    // ... 12 months total
]
```

---

### 5. getDocumentsByDepartment()
**Purpose**: Department-wise document analysis  
**Returns**: Array of department counts  
**Use Case**: Horizontal bar chart

```php
$data = $controller->getDocumentsByDepartment();
```

**Return Structure**:
```php
[
    ['department' => 'Legal Affairs', 'count' => 234],
    ['department' => 'Finance', 'count' => 189],
    ['department' => 'HR', 'count' => 156],
    // ...
]
```

---

## User Analytics

### 6. getTopUploaders($limit = 10)
**Purpose**: Get most active document uploaders  
**Parameters**: `$limit` - Number of users to return (default: 10)  
**Returns**: Array of top uploaders  
**Use Case**: Leaderboard, contributor recognition

```php
$data = $controller->getTopUploaders(5);  // Top 5
$data = $controller->getTopUploaders(50); // Top 50 for export
```

**Return Structure**:
```php
[
    [
        'user_id' => 12,
        'name' => 'John Doe',
        'full_name' => 'John Doe',
        'department' => 'Legal Affairs',
        'document_count' => 145
    ],
    // ...
]
```

---

### 7. getUserActivityReport($startDate = null, $endDate = null)
**Purpose**: Comprehensive user activity analysis  
**Parameters**:
- `$startDate` - Start date (Y-m-d format, optional)
- `$endDate` - End date (Y-m-d format, optional)  
**Returns**: Array of user activities with counts  
**Use Case**: User productivity reports, CSV export

```php
$data = $controller->getUserActivityReport('2024-01-01', '2024-01-31');
```

**Return Structure**:
```php
[
    [
        'user_id' => 12,
        'full_name' => 'John Doe',
        'email' => 'john@example.com',
        'department' => 'Legal Affairs',
        'action_count' => 245,
        'last_activity' => '2024-01-31 14:32:15'
    ],
    // ...
]
```

---

## Activity Analytics

### 8. getActivityByAction()
**Purpose**: Breakdown of activities by action type (30 days)  
**Returns**: Array of action counts  
**Use Case**: Bar chart showing activity distribution

```php
$data = $controller->getActivityByAction();
```

**Return Structure**:
```php
[
    ['action' => 'create', 'count' => 145],
    ['action' => 'update', 'count' => 234],
    ['action' => 'delete', 'count' => 23],
    ['action' => 'login', 'count' => 456],
    ['action' => 'view', 'count' => 1234],
    ['action' => 'download', 'count' => 678]
]
```

---

### 9. getRecentActivities($limit = 10)
**Purpose**: Get most recent system activities  
**Parameters**: `$limit` - Number of activities (default: 10)  
**Returns**: Array of recent activities  
**Use Case**: Activity feed, recent events table

```php
$data = $controller->getRecentActivities(20);
```

**Return Structure**:
```php
[
    [
        'id' => 12345,
        'user_id' => 12,
        'username' => 'johndoe',
        'full_name' => 'John Doe',
        'action' => 'create',
        'description' => 'Created new document: Budget Report 2024',
        'created_at' => '2024-01-15 14:32:15'
    ],
    // ...
]
```

---

## Document Access Analytics

### 10. getDocumentAccessReport($startDate = null, $endDate = null)
**Purpose**: Track document access patterns  
**Parameters**:
- `$startDate` - Start date (optional)
- `$endDate` - End date (optional)  
**Returns**: Array of most accessed documents (Top 50)  
**Use Case**: Popular content analysis, CSV export

```php
$data = $controller->getDocumentAccessReport('2024-01-01', '2024-01-31');
```

**Return Structure**:
```php
[
    [
        'document_id' => 123,
        'title' => 'Budget Report 2024',
        'document_type' => 'report',
        'access_count' => 245,
        'last_accessed' => '2024-01-31 16:45:00'
    ],
    // ...
]
```

---

## Storage Analytics

### 11. getStorageByType()
**Purpose**: Analyze storage usage by document type  
**Returns**: Array of storage breakdown  
**Use Case**: Storage optimization, capacity planning

```php
$data = $controller->getStorageByType();
```

**Return Structure**:
```php
[
    [
        'document_type' => 'ordinance',
        'total_size' => 524288000,  // bytes
        'document_count' => 450
    ],
    [
        'document_type' => 'resolution',
        'total_size' => 314572800,
        'document_count' => 320
    ],
    // ...
]
```

---

### 12. getMonthlyGrowth()
**Purpose**: Get 6-month growth statistics  
**Returns**: Array of monthly metrics  
**Use Case**: Trend analysis, growth charts

```php
$data = $controller->getMonthlyGrowth();
```

**Return Structure**:
```php
[
    [
        'month' => '2024-01',
        'documents_created' => 45,
        'active_users' => 28,
        'storage_used' => 524288000  // bytes
    ],
    [
        'month' => '2024-02',
        'documents_created' => 52,
        'active_users' => 31,
        'storage_used' => 838860800
    ],
    // ... 6 months total
]
```

---

## Export Functionality

### 13. exportToCSV($reportType, $data)
**Purpose**: Export report data to CSV file  
**Parameters**:
- `$reportType` - Type identifier for filename
- `$data` - Array of data to export  
**Returns**: Triggers file download (exits script)  
**Use Case**: Generate downloadable CSV reports

```php
$data = $controller->getUserActivityReport();
$controller->exportToCSV('user_activity', $data);
// Script exits after download headers sent
```

**Features**:
- UTF-8 encoding with BOM
- Dynamic column headers from array keys
- Timestamped filenames
- Excel-compatible format
- Automatic file download

**Generated Filename Example**:
```
user_activity_report_2024-01-15_14-32-15.csv
```

---

## Usage Examples

### Example 1: Display Dashboard
```php
<?php
require_once 'modules/reports-analytics/controllers/ReportController.php';

$controller = new ReportController();

// Get all dashboard data
$stats = $controller->getDashboardStats();
$docsByType = $controller->getDocumentsByType();
$timeline = $controller->getDocumentsTimeline();
$topUploaders = $controller->getTopUploaders(5);

// Display in view
?>
<h1>Dashboard</h1>
<p>Total Documents: <?php echo $stats['total_documents']; ?></p>
<!-- ... render charts ... -->
```

---

### Example 2: Export User Activity
```php
<?php
require_once 'modules/reports-analytics/controllers/ReportController.php';

$controller = new ReportController();

// Get date parameters
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Get and export data
$data = $controller->getUserActivityReport($startDate, $endDate);
$controller->exportToCSV('user_activity', $data);
// Script exits, CSV downloads
```

---

### Example 3: Chart.js Integration
```javascript
// Fetch data from controller
<?php
$controller = new ReportController();
$data = $controller->getDocumentsByType();
?>

// Render chart
new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($data, 'document_type')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($data, 'count')); ?>,
            backgroundColor: ['#3b82f6', '#22c55e', '#ef4444', '#eab308']
        }]
    }
});
```

---

### Example 4: AJAX Report Loading
```javascript
// JavaScript to load report dynamically
async function loadReport(reportType, startDate, endDate) {
    const params = new URLSearchParams({
        report: reportType,
        start_date: startDate,
        end_date: endDate
    });
    
    const response = await fetch(`/api/reports?${params}`);
    const data = await response.json();
    
    return data;
}

// Usage
const report = await loadReport('user_activity', '2024-01-01', '2024-01-31');
renderTable(report);
```

---

## Error Handling

All controller methods use try-catch blocks:

```php
try {
    $data = $controller->getDashboardStats();
} catch (Exception $e) {
    // Log error
    error_log("Report error: " . $e->getMessage());
    
    // Return empty/default data
    $data = [];
}
```

---

## Performance Considerations

1. **Caching**: Consider caching dashboard stats for 5-10 minutes
2. **Pagination**: Use LIMIT clauses for large datasets
3. **Indexes**: Ensure database indexes on:
   - `documents.upload_date`
   - `documents.document_type`
   - `documents.status`
   - `activity_logs.created_at`
   - `activity_logs.action`
4. **Query Optimization**: Use JOINs instead of multiple queries

---

## Security Notes

1. **Permission Check**: All methods require officer+ role
2. **SQL Injection**: Uses prepared statements
3. **XSS Prevention**: Sanitize output in views
4. **Date Validation**: Validate date parameters
5. **Export Limits**: Limit export size to prevent memory issues

---

## Chart.js Configuration Reference

### Colors Used
```javascript
const chartColors = {
    blue: 'rgb(59, 130, 246)',
    green: 'rgb(34, 197, 94)',
    red: 'rgb(239, 68, 68)',
    yellow: 'rgb(234, 179, 8)',
    purple: 'rgb(168, 85, 247)',
    indigo: 'rgb(99, 102, 241)',
    pink: 'rgb(236, 72, 153)',
    orange: 'rgb(249, 115, 22)'
};
```

### Chart Types Used
- **Doughnut**: Documents by Type
- **Pie**: Documents by Status
- **Line**: Upload Timeline
- **Bar**: Activity by Action
- **Horizontal Bar**: Documents by Department

---

## CSV Export Format

### Headers
First row contains column names from array keys.

### Example CSV Output
```csv
user_id,full_name,email,department,action_count,last_activity
12,"John Doe","john@example.com","Legal Affairs",245,"2024-01-31 14:32:15"
15,"Jane Smith","jane@example.com","Finance",189,"2024-01-31 16:45:22"
```

---

## Future API Enhancements

- [ ] REST API endpoints for external integrations
- [ ] Real-time data with WebSockets
- [ ] GraphQL support
- [ ] Rate limiting on export endpoints
- [ ] Scheduled report generation
- [ ] Email report delivery
- [ ] PDF export with charts

---

**Last Updated**: 2024  
**Version**: 1.0.0
