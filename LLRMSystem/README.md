# Legislative Records Management System (LRMS)

**Version:** 1.0.0  
**Module:** Module 6 - Central Document Repository  
**Date:** November 20, 2025

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Architecture](#architecture)
- [Installation](#installation)
- [Folder Structure](#folder-structure)
- [Technology Stack](#technology-stack)
- [Modules](#modules)
- [API Integration](#api-integration)
- [Security](#security)
- [Usage Guide](#usage-guide)
- [Development Guidelines](#development-guidelines)

## 🎯 Overview

The Legislative Records Management System (LRMS) is the **central document repository** for the LGU Legislative Information System. It serves as Module 6, receiving, storing, versioning, searching, and retrieving ALL legislative documents from 9 external modules.

### Core Functions:
- ✅ **Document Storage** - Centralized repository for all legislative documents
- ✅ **Version Control** - Track document versions and changes
- ✅ **Advanced Search** - Powerful search with filters and facets
- ✅ **API Integration** - RESTful API for 9 external modules
- ✅ **Access Control** - Role-based permissions (RBAC)
- ✅ **Audit Trail** - Complete activity logging

## ✨ Features

### For Users:
- 📤 **Upload Documents** - Drag & drop file upload with progress tracking
- 🔍 **Advanced Search** - Search by title, reference, keywords, dates, types
- 📊 **Dashboard** - Real-time statistics and recent activity
- 📁 **Document Management** - View, download, edit, version documents
- 🏷️ **Tagging System** - Organize documents with custom tags
- 🔗 **Document Linking** - Link related documents together
- 📈 **Reports & Analytics** - Generate custom reports

### For Administrators:
- 👥 **User Management** - Manage users, roles, and permissions
- 🔐 **Access Control** - Fine-grained permission system
- 📝 **Activity Logs** - Monitor all system activities
- 🔌 **API Management** - Manage API keys for integrations
- 💾 **Storage Management** - Monitor storage usage
- ⚙️ **System Settings** - Configure system parameters

### For Developers:
- 🔗 **RESTful API** - Well-documented API endpoints
- 📚 **Clean Code** - Follows SOLID principles
- 🧩 **Modular Design** - Feature-based architecture
- 🔒 **Security First** - PDO, password hashing, CSRF protection
- 📖 **Documentation** - Comprehensive inline documentation

## 🏗️ Architecture

### Modular Feature-Based Structure

```
modules/
├── core/                    # Shared functionality
├── authentication/          # Auth feature
├── dashboard/               # Dashboard feature
├── document-management/     # Document CRUD
├── search/                  # Search feature
├── integration-ordinances/  # Module 1 integration
├── integration-sessions/    # Module 2 integration
├── ... (7 more integrations)
├── reports-analytics/       # Reports feature
├── user-management/         # User admin feature
└── audit-logging/           # Logging feature
```

Each module contains:
- `controllers/` - Request handling
- `models/` - Data access layer
- `services/` - Business logic
- `views/` - User interface
- `api/` - API endpoints (for integrations)

## 🚀 Installation

### Prerequisites:
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional, for dependencies)

### Step 1: Clone Repository
```bash
cd c:\xampp\htdocs
git clone https://github.com/your-org/LLRMSystem.git
cd LLRMSystem
```

### Step 2: Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE lrms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Import schema
mysql -u root -p lrms_db < database/schema.sql
```

### Step 3: Configuration
```bash
# Copy config template
cp modules/core/config/config.example.php modules/core/config/config.php

# Edit database credentials
# Update config.php with your database settings
```

### Step 4: Set Permissions
```bash
# Storage directories
chmod -R 755 storage/
chmod -R 755 storage/documents/
chmod -R 755 storage/versions/

# Cache directories (if using)
chmod -R 755 cache/
```

### Step 5: Access System
```
http://localhost/LLRMSystem/modules/authentication/views/login.php
```

Default credentials:
- Email: admin@lgu.gov.ph
- Password: admin123

## 📂 Folder Structure

```
LLRMSystem/
│
├── modules/                      # Feature modules
│   ├── core/                     # Core shared functionality
│   │   ├── config/               # Configuration files
│   │   ├── middleware/           # Request middlewares
│   │   ├── utils/                # Utility classes
│   │   └── layouts/              # Shared view layouts
│   │
│   ├── authentication/           # Authentication module
│   │   ├── controllers/
│   │   ├── models/
│   │   ├── services/
│   │   └── views/
│   │
│   ├── document-management/      # Document CRUD module
│   │   ├── controllers/
│   │   ├── models/
│   │   ├── services/
│   │   └── views/
│   │
│   └── [... other modules]
│
├── public/                       # Public web root
│   ├── index.php                 # Entry point
│   ├── .htaccess                 # URL rewriting
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
│
├── storage/                      # File storage (secure)
│   ├── documents/
│   ├── versions/
│   └── temp/
│
├── database/                     # Database files
│   ├── schema.sql
│   ├── migrations/
│   └── seeds/
│
├── STRUCTURE.md                  # Architecture documentation
└── README.md                     # This file
```

## 💻 Technology Stack

### Frontend:
- **HTML5** - Semantic markup
- **Tailwind CSS 4** - Utility-first CSS framework
- **JavaScript ES6+** - Modern vanilla JavaScript
- **Chart.js** - Data visualization
- **Bootstrap Icons** - Icon library

### Backend:
- **PHP 8.x** - Server-side language
- **MySQL** - Relational database
- **PDO** - Database abstraction layer
- **MVC Architecture** - Design pattern
- **RESTful API** - API design

### Security:
- **Password Hashing** - bcrypt
- **Prepared Statements** - SQL injection prevention
- **CSRF Tokens** - Cross-site request forgery protection
- **XSS Protection** - Output sanitization
- **RBAC** - Role-based access control
- **API Keys** - Module authentication

## 📦 Modules

### Core Modules:

#### 1. **Authentication** (`modules/authentication/`)
- User login/logout
- Registration
- Password reset
- Session management

#### 2. **Dashboard** (`modules/dashboard/`)
- Statistics cards
- Charts and graphs
- Recent activity
- Quick actions

#### 3. **Document Management** (`modules/document-management/`)
- Upload documents
- View/edit documents
- Version control
- Document linking

#### 4. **Search** (`modules/search/`)
- Full-text search
- Advanced filters
- Faceted search
- Search indexing

### Integration Modules:

#### 5-13. **External Module Integrations**
- `integration-ordinances/` - Module 1
- `integration-sessions/` - Module 2
- `integration-agendas/` - Module 3
- `integration-committees/` - Module 4
- `integration-voting/` - Module 5
- `integration-hearings/` - Module 7
- `integration-archives/` - Module 8
- `integration-consultations/` - Module 9
- `integration-research/` - Module 10

### Admin Modules:

#### 14. **Reports & Analytics** (`modules/reports-analytics/`)
- Custom reports
- Data export
- Charts and visualizations

#### 15. **User Management** (`modules/user-management/`)
- User CRUD
- Role management
- Permission settings

#### 16. **Audit Logging** (`modules/audit-logging/`)
- Activity logs
- Access logs
- System monitoring

## 🔌 API Integration

### Authentication:
All API requests require an API key in the header:
```
X-API-Key: your-module-api-key
```

### Endpoints:

#### Upload Document (POST)
```
POST /modules/integration-ordinances/api/ordinances.php
Content-Type: multipart/form-data

{
    "ordinance_id": 123,
    "title": "Ordinance Title",
    "document_date": "2025-11-20",
    "file": <file_binary>,
    "metadata": {...}
}

Response:
{
    "success": true,
    "document_id": 456,
    "reference_number": "ORD-2025-042",
    "file_url": "https://..."
}
```

#### Get Document (GET)
```
GET /modules/integration-ordinances/api/ordinances.php?ordinance_id=123

Response:
{
    "success": true,
    "data": {
        "document_id": 456,
        "reference_number": "ORD-2025-042",
        "title": "...",
        "file_url": "...",
        ...
    }
}
```

#### Update Document (PUT)
```
PUT /modules/integration-ordinances/api/ordinances.php?ordinance_id=123
Content-Type: application/json

{
    "title": "Updated Title",
    "status": "approved"
}
```

#### Delete Document (DELETE)
```
DELETE /modules/integration-ordinances/api/ordinances.php?ordinance_id=123
```

## 🔒 Security

### Implemented Security Measures:

1. **SQL Injection Prevention**
   - PDO prepared statements
   - Parameterized queries
   - Input validation

2. **XSS Protection**
   - `htmlspecialchars()` on all outputs
   - Content Security Policy headers
   - Input sanitization

3. **CSRF Protection**
   - CSRF tokens on all forms
   - Token validation
   - SameSite cookies

4. **Authentication**
   - Password hashing (bcrypt)
   - Session management
   - Session timeout
   - Remember me tokens

5. **Authorization**
   - Role-based access control (RBAC)
   - Permission checks
   - Resource ownership validation

6. **File Upload Security**
   - File type validation
   - File size limits
   - Virus scanning (recommended)
   - Secure file storage

7. **API Security**
   - API key authentication
   - Rate limiting (recommended)
   - HTTPS only (production)

## 📖 Usage Guide

### For End Users:

#### Uploading a Document:
1. Navigate to **Documents > Upload Document**
2. Drag & drop file or click to browse
3. Fill in document information
4. Select document type and status
5. Add tags for organization
6. Click "Upload Document"

#### Searching Documents:
1. Navigate to **Search**
2. Enter search keywords
3. Apply filters (type, status, date range)
4. View results
5. Click "View" or "Download"

### For Administrators:

#### Managing Users:
1. Navigate to **Management > User Management**
2. Click "Add User" to create new user
3. Assign roles and permissions
4. Save changes

#### Viewing Logs:
1. Navigate to **Management > Activity Logs**
2. Filter by date, user, or action
3. Export logs if needed

## 🛠️ Development Guidelines

### Code Standards:

1. **File Size Limit**: Max 300 lines per file
2. **Function Size**: Max 30 lines per function
3. **Class Responsibility**: Single Responsibility Principle
4. **Naming**: Descriptive, meaningful names
5. **Comments**: Explain WHY, not WHAT

### Best Practices:

```php
// ✅ GOOD: Clean, focused function
public function create($data) {
    $this->validate($data);
    $document = $this->repository->save($data);
    $this->logger->log('Document created', $document->id);
    return $document;
}

// ❌ BAD: Doing too much
public function create($data) {
    // Validation, saving, logging, emailing, etc.
    // 100+ lines of code
}
```

### Testing:
```bash
# Run tests
php vendor/bin/phpunit

# Code coverage
php vendor/bin/phpunit --coverage-html coverage/
```

## 📞 Support

- **Documentation**: See `/docs` folder
- **Issues**: GitHub Issues
- **Email**: support@lgu.gov.ph

## 📄 License

Copyright © 2025 LGU Legislative Office. All rights reserved.

---

**Built with ❤️ for better legislative document management**
