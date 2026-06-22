# Input Data Sanitization — Security Implementation Guide

## Overview

This document describes the defense-in-depth input sanitization implementation across the LLRM System. All user inputs are sanitized at entry points using centralized utility classes to prevent SQL injection, XSS, command injection, and path traversal attacks.

## Core Utilities

### `modules/core/utils/Sanitizer.php`

Centralized static class providing type-specific sanitization methods:

| Method | Purpose |
|--------|---------|
| `Sanitizer::int($value, $min)` | Cast to integer, enforce minimum |
| `Sanitizer::float($value)` | Cast to float |
| `Sanitizer::bool($value)` | Cast to boolean |
| `Sanitizer::string($value, $options)` | Strip tags, encode special chars, optional maxLength |
| `Sanitizer::plainText($value)` | Strip tags, remove encoded entities (for names, titles) |
| `Sanitizer::richText($value)` | Allow basic HTML (b, i, u, em, strong, p, br), strip dangerous tags |
| `Sanitizer::email($value)` | Sanitize email format |
| `Sanitizer::enum($value, $allowed, $default)` | Whitelist validation for enum values |
| `Sanitizer::date($value)` | Validate date format (Y-m-d) |
| `Sanitizer::filename($value)` | Strip path components, dangerous chars from filenames |
| `Sanitizer::array($values, $rule)` | Recursively sanitize array elements with a given rule |
| `Sanitizer::forHtml($value)` | Output escaping with `htmlspecialchars` for XSS prevention |

### `modules/core/utils/Request.php`

Helper class for accessing sanitized input:

| Method | Purpose |
|--------|---------|
| `Request::get($key, $default, $rule)` | Sanitized `$_GET` access |
| `Request::post($key, $default, $rule)` | Sanitized `$_POST` access |
| `Request::request($key, $default, $rule)` | Sanitized `$_REQUEST` access |
| `Request::json($key, $default, $rule)` | Sanitized JSON body access |
| `Request::postAll($rules)` | Bulk sanitized `$_POST` access |

## Global Inclusion

Both utilities are included globally via `modules/core/config/config.php`:

```php
require_once __DIR__ . '/../utils/Sanitizer.php';
require_once __DIR__ . '/../utils/Request.php';
```

This ensures `Sanitizer` and `Request` are available in all modules that include the config.

## Sanitization Coverage by Module

### Authentication
- **LoginController**: `Sanitizer::email`, `Sanitizer::string` for email/password
- **RegisterController**: `Sanitizer::plainText`, `Sanitizer::email`, `Sanitizer::enum` for role, `Sanitizer::string` for passwords
- **ForgotPasswordController**: `Sanitizer::email`
- **VerifyOtpController**: `Sanitizer::string` with maxLength

### User Management
- **UserController**: `Sanitizer::int`, `Sanitizer::plainText`, `Sanitizer::enum` for filters and pagination
- **APIs (create-user, update-user, update-profile, change-password, delete-user, get-user)**: Full sanitization of all POST/GET inputs
- **update-settings**: Dynamic SQL key whitelisting + `Sanitizer::string` for values

### Document Management
- **DocumentController**: `Sanitizer::int`, `Sanitizer::enum`, `Sanitizer::plainText`, `Sanitizer::richText`, `Sanitizer::date` for all GET/POST/FILES inputs
- **APIs (approve, delete, bulk-delete, update, create-tag, assign-tag, remove-tag, create-link, delete-link, revert-version, download, download-version, generate_reference, get_details, verify-document-access, update-with-file, export, upload)**: All inputs sanitized with appropriate methods
- **FileStorageService**: Extension whitelist, MIME type cross-verification, file size limit (10MB), path traversal protection on upload/delete/get

### Search & Public Portal
- **SearchController**: `Sanitizer::plainText` for query, `Sanitizer::enum` for mode/status, `Sanitizer::date` for date filters
- **public_search.php**: Same sanitization as SearchController
- **public_document.php**: `Sanitizer::int` for document ID

### Core Admin
- **admin-management.php**: `Sanitizer::enum` for action/role/status/sort fields, `Sanitizer::int` for user IDs, `Sanitizer::plainText` for search/department, `Sanitizer::email` for email
- **audit-logs.php**: `Sanitizer::int` for user_id/pagination, `Sanitizer::plainText` for search/action/table, `Sanitizer::date` for date filters
- **system-config.php**: `Sanitizer::enum` for action, `Sanitizer::array` with string rule for config data
- **database-backup.php**: `Sanitizer::enum` for action, `Sanitizer::filename` for backup filenames, `Sanitizer::int` for retention days
- **SuperAdminController**: `escapeshellarg()` on all shell command parameters, `Sanitizer::filename()` on user-supplied filenames for path traversal prevention

### Reports & Analytics
- **generate-report.php**: `Sanitizer::enum` for report type/format, `Sanitizer::date` for date range
- **export.php**: Same sanitization as generate-report
- **refresh-data.php**: `Sanitizer::date` for date parameters
- **schedule-report.php**: `Sanitizer::enum` for report type/frequency, `Sanitizer::array` with email rule for recipients

### Integration
- **import.php**: `Sanitizer::int` for record ID
- **receive.php**: `Sanitizer::plainText` for module_type/source_system/external_id/title/tags, `Sanitizer::richText` for summary, `Sanitizer::date` for document_date

### Other Modules
- **Dashboard stats.php**: `Sanitizer::enum` for action, `Sanitizer::int` for limit
- **AuditController**: `Sanitizer::int` for user_id/pagination, `Sanitizer::plainText` for search/action/table_name, `Sanitizer::date` for date filters
- **Chatbot chat.php**: `Sanitizer::plainText` for message, `Sanitizer::array` for history
- **Notifications notifications.php**: `Sanitizer::enum` for action, `Sanitizer::int` for notification IDs/limit/offset

## Security Hardening

### Command Injection Prevention
- `SuperAdminController::createBackup()` and `restoreBackup()` use `escapeshellarg()` on all shell command parameters (host, username, password, database, file paths)
- `restoreBackup()` and `deleteBackup()` use `Sanitizer::filename()` to prevent path traversal via user-supplied filenames

### File Upload Hardening
- **Extension whitelist**: Only pdf, doc, docx, xls, xlsx, ppt, pptx, jpg, jpeg, png, gif allowed
- **MIME type verification**: Uses `finfo_file()` for server-side MIME detection, cross-checked against extension
- **File size limit**: 10MB maximum enforced server-side
- **Path traversal prevention**: Document type directory sanitized with `preg_replace('/[^a-zA-Z0-9_-]/', '', $documentType)`
- **Path traversal on delete/get**: `realpath()` validation ensures file paths resolve within storage directory
- **Unique filenames**: Generated with `time() + random_bytes()` — no user input in stored filename

### Dynamic SQL Protection
- `update-settings.php`: Settings keys validated against a whitelist of allowed keys before use in SQL
- `SuperAdminController::getAdministrators()`: Sort column validated against `$allowedSortColumns` whitelist
- All database queries use PDO prepared statements

### Existing Security (Preserved)
- CSRF protection via `CsrfMiddleware::requireValidToken()` on all POST endpoints
- Role-based access control via `PermissionMiddleware`
- Session-based authentication checks
- Public portal rate limiting (60 requests/minute per IP)
- Public portal forces `status = 'approved'` server-side — cannot be overridden by query params

## Testing

All modified files pass `php -l` syntax checks. Run the following to verify:

```powershell
# Syntax check all modified files
php -l modules\core\utils\Sanitizer.php
php -l modules\core\utils\Request.php
php -l modules\authentication\controllers\LoginController.php
php -l modules\authentication\controllers\RegisterController.php
php -l modules\authentication\controllers\ForgotPasswordController.php
php -l modules\authentication\controllers\VerifyOtpController.php
php -l modules\user-management\controllers\UserController.php
php -l modules\user-management\api\create-user.php
php -l modules\user-management\api\update-user.php
php -l modules\user-management\api\update-profile.php
php -l modules\user-management\api\change-password.php
php -l modules\user-management\api\delete-user.php
php -l modules\user-management\api\get-user.php
php -l modules\user-management\api\update-settings.php
php -l modules\document-management\controllers\DocumentController.php
php -l modules\document-management\services\FileStorageService.php
php -l modules\document-management\api\approve.php
php -l modules\document-management\api\delete.php
php -l modules\document-management\api\bulk-delete.php
php -l modules\document-management\api\update.php
php -l modules\document-management\api\create-tag.php
php -l modules\document-management\api\assign-tag.php
php -l modules\document-management\api\remove-tag.php
php -l modules\document-management\api\create-link.php
php -l modules\document-management\api\delete-link.php
php -l modules\document-management\api\revert-version.php
php -l modules\document-management\api\download.php
php -l modules\document-management\api\download-version.php
php -l modules\document-management\api\generate_reference.php
php -l modules\document-management\api\get_details.php
php -l modules\document-management\api\verify-document-access.php
php -l modules\document-management\api\update-with-file.php
php -l modules\document-management\api\export.php
php -l modules\document-management\api\upload.php
php -l modules\search\controllers\SearchController.php
php -l modules\public-portal\api\public_search.php
php -l modules\public-portal\api\public_document.php
php -l modules\core\api\admin-management.php
php -l modules\core\api\audit-logs.php
php -l modules\core\api\system-config.php
php -l modules\core\api\database-backup.php
php -l modules\core\controllers\SuperAdminController.php
php -l modules\reports-analytics\api\generate-report.php
php -l modules\reports-analytics\api\export.php
php -l modules\reports-analytics\api\refresh-data.php
php -l modules\reports-analytics\api\schedule-report.php
php -l modules\integration\api\import.php
php -l modules\integration\api\receive.php
php -l modules\chatbot\api\chat.php
php -l modules\notifications\api\notifications.php
php -l modules\dashboard\api\stats.php
php -l modules\audit\controllers\AuditController.php
```

## Developer Guidelines

When adding new endpoints or modifying existing ones:

1. **Always use Sanitizer methods** — never access `$_GET`, `$_POST`, `$_REQUEST`, or JSON body directly
2. **Use `Sanitizer::enum()`** for any value that should match a fixed set of options
3. **Use `Sanitizer::int()`** for IDs and pagination parameters
4. **Use `Sanitizer::plainText()`** for short text fields (names, titles, search queries)
5. **Use `Sanitizer::richText()`** for description fields that may contain basic HTML
6. **Use `Sanitizer::date()`** for date inputs
7. **Use `Sanitizer::filename()`** for any user-supplied filename used in file operations
8. **Use `Sanitizer::array()`** for bulk operations with arrays of IDs
9. **Whitelist dynamic SQL column names** — never insert user input directly into SQL column identifiers
10. **Use `escapeshellarg()`** for any value passed to shell commands
11. **Use `Sanitizer::forHtml()`** when outputting user-supplied data in HTML views
12. **Ensure `config.php` is included** before using Sanitizer in any new file
