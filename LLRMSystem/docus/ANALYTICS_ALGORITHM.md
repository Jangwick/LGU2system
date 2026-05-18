# Analytics Algorithm Documentation

This document describes the algorithms and logic used in the LRMS analytics and reporting system.

## 1. Analytics Aggregation

### Document Statistics
- **Location**: `modules/reports-analytics/controllers/ReportController.php`
- **Method**: `getDocumentStatistics()`
- **Algorithm**:
  ```php
  // Aggregates document counts by status
  SELECT status, COUNT(*) as count 
  FROM legislative_documents 
  WHERE deleted_at IS NULL 
  GROUP BY status
  ```
- **Purpose**: Provides overview of document distribution across different statuses (draft, pending, approved, rejected, archived)

### User Activity Aggregation
- **Location**: `modules/reports-analytics/controllers/ReportController.php`
- **Method**: `getUserActivityReport()`
- **Algorithm**:
  ```php
  // Aggregates user actions within date range
  SELECT user_id, action, COUNT(*) as count
  FROM activity_logs
  WHERE created_at BETWEEN start_date AND end_date
  GROUP BY user_id, action
  ORDER BY count DESC
  ```
- **Purpose**: Tracks user engagement and activity patterns over time

### Document Access Aggregation
- **Location**: `modules/reports-analytics/controllers/ReportController.php`
- **Method**: `getDocumentAccessReport()`
- **Algorithm**:
  ```php
  // Aggregates document access patterns
  SELECT document_id, COUNT(*) as access_count
  FROM activity_logs
  WHERE action = 'document_view' OR action = 'document_download'
  GROUP BY document_id
  ORDER BY access_count DESC
  ```
- **Purpose**: Identifies most frequently accessed documents

## 2. Search Ranking

### Search Relevance Score
- **Location**: `modules/search/services/SearchService.php`
- **Method**: `searchDocuments()`
- **Algorithm**:
  ```php
  // Multi-field weighted search with relevance scoring
  score = (title_match * 3.0) + 
          (reference_match * 2.5) + 
          (description_match * 1.5) + 
          (tags_match * 1.0) +
          (content_match * 0.5)
  
  // Results sorted by relevance score (descending)
  ORDER BY score DESC, created_at DESC
  ```
- **Purpose**: Provides intelligent search ranking based on field importance

### Search Suggestions
- **Location**: `modules/search/services/SearchService.php`
- **Method**: `getSuggestions()`
- **Algorithm**:
  ```php
  // Prefix matching for autocomplete
  SELECT title, reference_number
  FROM legislative_documents
  WHERE (title LIKE :prefix OR reference_number LIKE :prefix)
  AND status = 'approved'
  LIMIT 10
  ```
- **Purpose**: Real-time search suggestions for improved user experience

## 3. Data Validation

### Document Validation Rules
- **Location**: `modules/core/utils/Validator.php`
- **Methods**:
  - `documentType()`: Validates against allowed document types
  - `documentStatus()`: Validates against allowed statuses
  - `referenceNumber()`: Validates format: TYPE-YYYY-NNN
  - `confidentialityLevel()`: Validates confidentiality levels
  - `fileExtension()`: Validates file extensions
  - `fileSize()`: Validates file size limits (10MB default)

### Naming Convention Validation
- **Location**: `modules/document-management/models/Document.php`
- **Method**: `validateNamingConvention()`
- **Algorithm**:
  ```php
  // Enforces format: [Type]-[ReferenceNumber]: [Title]
  expected_prefix = ucfirst(document_type) + '-' + reference_number + ': '
  
  if (strpos(title, expected_prefix) !== 0) {
      return validation_error
  }
  ```
- **Purpose**: Ensures consistent document naming across the system

### Reference Number Uniqueness
- **Location**: `modules/document-management/services/DocumentService.php`
- **Method**: `createDocument()`
- **Algorithm**:
  ```php
  // Checks for duplicate reference numbers
  SELECT id FROM legislative_documents WHERE reference_number = ?
  
  if (result exists) {
      return error: "Reference number already exists"
  }
  ```
- **Purpose**: Prevents duplicate reference numbers

## 4. Date Range Filtering

### 5-Year Data Range
- **Location**: `modules/reports-analytics/controllers/ReportController.php`
- **Method**: Various report methods
- **Algorithm**:
  ```php
  // Uses 60-month (5-year) window for analytics
  date_from = CURRENT_DATE - INTERVAL 60 MONTH
  date_to = CURRENT_DATE
  
  SELECT * FROM legislative_documents
  WHERE created_at BETWEEN date_from AND date_to
  ```
- **Purpose**: Provides consistent 5-year historical data view

### No Future Dates Validation
- **Location**: `modules/core/utils/Validator.php`
- **Method**: `dateNotFuture()`
- **Algorithm**:
  ```php
  // Prevents future date entries
  if (strtotime(input_date) > time()) {
      return error: "Date cannot be in the future"
  }
  ```
- **Purpose**: Ensures data integrity by preventing future-dated entries

## 5. Percentage Calculations

### Document Status Distribution
- **Location**: `modules/reports-analytics/controllers/ReportController.php`
- **Method**: `getDocumentStatistics()`
- **Algorithm**:
  ```php
  total_documents = COUNT(*)
  status_count = COUNT(CASE WHEN status = X THEN 1 END)
  
  percentage = (status_count / total_documents) * 100
  
  // Used for donut chart visualization
  return {status, count, percentage}
  ```
- **Purpose**: Provides percentage breakdown for visual analytics

### Activity Trend Calculation
- **Location**: `modules/reports-analytics/controllers/ReportController.php`
- **Method**: `getUserActivityReport()`
- **Algorithm**:
  ```php
  // Calculates activity trend over time
  period_count = COUNT(CASE WHEN date_in_period THEN 1 END)
  total_count = COUNT(*)
  
  trend_percentage = (period_count / total_count) * 100
  
  // Compares with previous period for trend direction
  if (current_percentage > previous_percentage) {
      trend = 'increasing'
  } else {
      trend = 'decreasing'
  }
  ```
- **Purpose**: Identifies activity trends and patterns

## 6. Encryption Algorithm

### AES-256 Encryption
- **Location**: `modules/document-management/services/EncryptionService.php`
- **Method**: `encryptFile()`, `decryptFile()`
- **Algorithm**:
  ```php
  // AES-256-CBC encryption
  cipher_method = 'AES-256-CBC'
  key = 32-byte encryption key
  iv = 16-byte random initialization vector
  
  encrypted_content = openssl_encrypt(data, cipher_method, key, 0, iv)
  final_output = base64_encode(iv + encrypted_content)
  
  // Decryption reverses the process
  decoded = base64_decode(encrypted_data)
  iv = substring(decoded, 0, 16)
  encrypted = substring(decoded, 16)
  decrypted = openssl_decrypt(encrypted, cipher_method, key, 0, iv)
  ```
- **Purpose**: Secures document files at rest with AES-256 encryption

## 7. Access Control Algorithm

### Role-Based Access Control (RBAC)
- **Location**: `modules/document-management/api/verify-document-access.php`
- **Algorithm**:
  ```php
  access_rules = {
      'public': ['viewer', 'staff', 'officer', 'administrator', 'super_admin'],
      'internal': ['staff', 'officer', 'administrator', 'super_admin'],
      'confidential': ['officer', 'administrator', 'super_admin'],
      'restricted': ['super_admin']
  }
  
  if (user_role not in access_rules[confidentiality_level]) {
      return access_denied
  }
  ```
- **Purpose**: Enforces confidentiality-based access restrictions

## 8. Session Management

### Session Timeout Algorithm
- **Location**: `modules/core/middleware/SessionTimeoutMiddleware.php`
- **Method**: `checkSessionTimeout()`
- **Algorithm**:
  ```php
  session_timeout = 120 seconds (2 minutes)
  
  if (time() - $_SESSION['last_activity'] > session_timeout) {
      destroy_session()
      redirect_to_login()
  }
  
  // Update last activity on each request
  $_SESSION['last_activity'] = time()
  ```
- **Purpose**: Automatically expires inactive sessions for security

### OTP Expiration
- **Location**: `modules/core/config/config.php`
- **Configuration**: `OTP_EXPIRY_MINUTES = 1`
- **Algorithm**:
  ```php
  otp_expiry = 1 minute (60 seconds)
  
  if (time() - otp_creation_time > otp_expiry) {
      otp_invalid = true
  }
  ```
- **Purpose**: Time-limited OTP for secure authentication

## 9. Login Lockout Algorithm

### Incremental Lockout
- **Location**: `modules/core/utils/Security.php`
- **Method**: `checkLoginLockout()`
- **Algorithm**:
  ```php
  lockout_durations = [5, 15, 30, 60] // minutes
  attempt_threshold = 5
  
  failed_attempts = get_failed_attempts(ip, identifier)
  
  if (failed_attempts >= attempt_threshold) {
      lockout_index = floor((failed_attempts - attempt_threshold) / 3)
      lockout_duration = lockout_durations[min(lockout_index, 3)]
      
      if (last_failure_time + lockout_duration > current_time) {
          return locked
      }
  }
  ```
- **Purpose**: Prevents brute-force attacks with incremental lockout periods

## 10. Data Pagination

### Offset-Based Pagination
- **Location**: `modules/document-management/models/Document.php`
- **Method**: `getAll()`
- **Algorithm**:
  ```php
  page = requested_page_number
  per_page = items_per_page (default: 10)
  
  offset = (page - 1) * per_page
  
  SELECT * FROM legislative_documents
  WHERE deleted_at IS NULL
  LIMIT per_page OFFSET offset
  
  total_pages = ceil(total_count / per_page)
  ```
- **Purpose**: Efficiently paginates large datasets

## Summary

This document provides a comprehensive overview of the algorithms and logic used throughout the LRMS system. These algorithms ensure:

1. **Data Integrity**: Validation rules prevent invalid data entry
2. **Security**: Encryption, access control, and session management protect sensitive data
3. **Performance**: Efficient search and pagination optimize user experience
4. **Accuracy**: Proper aggregation and calculation methods ensure reliable analytics
5. **Consistency**: Standardized algorithms maintain uniform behavior across the system

For implementation details, refer to the specific files and methods mentioned in each section.
