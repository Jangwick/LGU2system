# Dynamic Path Configuration Guide

## Overview

The LRMS system now uses a **dynamic path detection system** that automatically adapts to any folder structure or nesting level. This means the application will work correctly regardless of:

- The root folder name (e.g., `LLRMSystem`, `myproject`, `legislature-system`)
- The number of parent folders (e.g., `htdocs/app`, `www/websites/prod/system`)
- The deployment location on the server

## How It Works

### PHP Backend Configuration

The system uses a centralized configuration file at `modules/core/config/config.php` that:

1. **Automatically detects the application's base directory** by walking up the directory tree until it finds the folder containing the `modules` directory
2. **Calculates the base URL** by comparing the file system path with the document root
3. **Defines constants** for all paths and URLs throughout the application

#### Key Constants Available

**Directory Paths (for file operations):**
- `BASE_PATH` - Application root directory
- `MODULES_PATH` - Modules directory
- `PUBLIC_PATH` - Public assets directory
- `STORAGE_PATH` - File storage directory
- `CORE_PATH` - Core module path
- `AUTH_PATH` - Authentication module path
- `DOCUMENTS_PATH` - Document management path
- And more...

**URL Paths (for HTML/JavaScript):**
- `BASE_URL` - Base application URL
- `ASSETS_URL` - Assets URL
- `CSS_URL`, `JS_URL`, `IMAGES_URL` - Asset subdirectories
- `AUTH_URL` - Authentication module URL
- `DOCUMENTS_URL` - Document management URL
- `DASHBOARD_URL`, `USERS_URL`, `REPORTS_URL`, etc.

**Common Page URLs:**
- `LOGIN_URL` - Login page
- `LOGOUT_URL` - Logout controller
- `DASHBOARD_INDEX_URL` - Dashboard home
- `DOCUMENTS_INDEX_URL` - Documents listing

#### Helper Functions

```php
// Generate a URL from a relative path
url('modules/dashboard/views/index.php')
// Returns: http://yourdomain.com/yourfolder/modules/dashboard/views/index.php

// Generate an asset URL
asset('js/main.js')
// Returns: http://yourdomain.com/yourfolder/public/assets/js/main.js

// Redirect to a URL
redirect($url);
redirectToLogin();
redirectToDashboard();
```

### JavaScript Frontend Configuration

The JavaScript configuration is loaded via `public/assets/js/config.js` and creates a global `App` object:

#### Available Properties

```javascript
// Base configuration
App.config.baseUrl          // Base URL of the application
App.config.urls.auth        // Authentication module URL
App.config.urls.documents   // Document management URL
App.config.urls.dashboard   // Dashboard URL
// ... and more

// API endpoints
App.config.api.documents    // Document API base
App.config.api.users        // User API base
// ... and more

// Assets
App.config.assets.js        // JavaScript assets URL
App.config.assets.css       // CSS assets URL
App.config.assets.images    // Images URL
```

#### Helper Functions

```javascript
// Build a URL
App.url('modules/dashboard/views/index.php')

// Build an asset URL
App.asset('js/custom.js')

// Build an API URL
App.apiUrl('documents', 'upload.php')
// Returns: /yourfolder/modules/document-management/api/upload.php

// Redirect helpers
App.redirect(url)
App.redirectToLogin()
App.redirectToDashboard()
```

## Usage Examples

### In PHP Files

#### Redirecting to Login
```php
// Old way (hardcoded):
header('Location: /LLRMSystem/modules/authentication/views/login.php');

// New way (dynamic):
require_once __DIR__ . '/../../core/config/config.php';
redirectToLogin();
```

#### Linking to a Page
```php
<!-- Old way (hardcoded): -->
<a href="/LLRMSystem/modules/dashboard/views/index.php">Dashboard</a>

<!-- New way (dynamic): -->
<a href="<?php echo DASHBOARD_INDEX_URL; ?>">Dashboard</a>
<!-- Or -->
<a href="<?php echo url('modules/dashboard/views/index.php'); ?>">Dashboard</a>
```

#### Loading Assets
```php
<!-- Old way (hardcoded): -->
<script src="/LLRMSystem/public/assets/js/main.js"></script>

<!-- New way (dynamic): -->
<script src="<?php echo asset('js/main.js'); ?>"></script>
```

### In JavaScript Files

#### Making API Calls
```javascript
// Old way (hardcoded):
fetch('/LLRMSystem/modules/document-management/api/upload.php', {
    method: 'POST',
    body: formData
});

// New way (dynamic):
fetch(App.apiUrl('documents', 'upload.php'), {
    method: 'POST',
    body: formData
});
```

#### Redirecting
```javascript
// Old way (hardcoded):
window.location.href = '/LLRMSystem/modules/dashboard/views/index.php';

// New way (dynamic):
App.redirectToDashboard();
// Or
window.location.href = App.config.urls.dashboard + '/views/index.php';
```

#### Downloading Files
```javascript
// Old way (hardcoded):
window.location.href = `/LLRMSystem/modules/document-management/api/download.php?id=${id}`;

// New way (dynamic):
window.location.href = App.apiUrl('documents', `download.php?id=${id}`);
```

## Migration Checklist

If you're adding new features or files, ensure you:

### For PHP Files:
- [ ] Include `config.php` at the top of your file
- [ ] Use constants like `DOCUMENTS_URL`, `BASE_PATH`, etc.
- [ ] Use helper functions like `url()`, `asset()`, `redirect()`
- [ ] Never hardcode `/LLRMSystem/` or absolute paths

### For JavaScript Files:
- [ ] Ensure `config.js` is loaded (it's in header.php by default)
- [ ] Use `App.config.urls.*` for module URLs
- [ ] Use `App.apiUrl()` for API endpoints
- [ ] Use `App.asset()` for asset files
- [ ] Never hardcode `/LLRMSystem/` or absolute paths

### For HTML/Views:
- [ ] Use `<?php echo CONSTANT_NAME; ?>` for links
- [ ] Use `<?php echo asset('path'); ?>` for assets
- [ ] Use `<?php echo url('path'); ?>` for custom URLs

## Benefits

1. **Portability**: Move the application to any folder without code changes
2. **Multi-instance**: Run multiple instances in different folders
3. **Environment Flexibility**: Works in development, staging, and production
4. **Maintainability**: Single source of truth for all paths
5. **Error Reduction**: No more broken links due to folder changes

## Troubleshooting

### Config Not Loading
If paths aren't working, ensure:
1. `config.php` exists in `modules/core/config/`
2. The file is included at the top of your PHP file
3. The `modules` folder is present in your application root

### JavaScript Config Issues
If JavaScript paths fail:
1. Check that `config.js` is loaded in header.php
2. Verify the script is loaded before other scripts that use it
3. Open browser console and check for `App` object

### Wrong Paths Generated
1. Clear any caches (browser, server)
2. Check that the folder structure has a `modules` directory
3. Verify document root is set correctly in your web server

## Best Practices

1. **Always use constants and helpers** - Never hardcode paths
2. **Include config.php early** - At the top of PHP files that need it
3. **Test in different environments** - Verify paths work locally and on server
4. **Use meaningful variable names** - When building dynamic URLs
5. **Keep the structure intact** - Don't move the `modules` folder

## Support

For issues or questions about the dynamic path system, please refer to:
- `/docus/STRUCTURE.md` - Application structure documentation
- `/docus/IMPLEMENTATION_SUMMARY.md` - Implementation details
