# VDMsystem - Voting and Decision-Making System

## LGU Module 5: Legislative Voting and Decision-Making System

**Module Code:** LGU-MOD-05  
**Version:** 1.0.0  
**Tech Stack:** HTML5, TailwindCSS 3.x, Vanilla JavaScript (ES6+), PHP 8.1+, MySQL 8.0+  
**Theme:** Blue (to differentiate from other LGU modules)

---

## Overview

The Voting and Decision-Making System (VDMsystem) is a comprehensive legislative voting management platform designed for the City Government of Valenzuela. It provides tools for managing legislative documents, routing workflows, conducting voting sessions, and generating reports.

## ✅ Implementation Status

| Module | Status | Description |
|--------|--------|-------------|
| Core Config | ✅ Complete | Database, paths, helpers |
| Layouts | ✅ Complete | Header, footer, sidebar, navbar |
| Authentication | ✅ Complete | Login, logout, session management |
| Dashboard | ✅ Complete | Statistics, charts, recent activity |
| Voting | ✅ Complete | Sessions, cast vote, results |
| Documents | ✅ Complete | CRUD, filtering, status workflow |
| Reports | ✅ Complete | Analytics, charts, export |
| Public Assets | ✅ Complete | CSS, JavaScript |

## Core Subsystems

1. **Dashboard** - Welcome banner, statistics, charts, recent activity
2. **Voting Sessions** - Create, manage, conduct real-time voting
3. **Cast Vote Interface** - Visual vote buttons, progress tracking
4. **Results Display** - Vote counts, percentages, individual votes
5. **Legislative Documents** - CRUD, version control, status workflow
6. **Reports & Analytics** - Date filtering, charts, participation stats

## Integration

This module integrates with other LGU modules:
- MOD-01: Ordinance and Resolution Tracking
- MOD-02: Session and Meeting Management
- MOD-03: Legislative Agenda and Calendar
- MOD-04: Committee Management System
- MOD-06: Legislative Records Management (LLRMSystem)
- MOD-07: Public Hearing Management
- MOD-08: Legislative Archives
- MOD-09: Legislative Research and Analysis
- MOD-10: Public Consultation Management

## Installation

1. Import the database schema from `database/vdm_db.sql`
2. Configure database settings in `modules/core/config/database.php`
3. Ensure XAMPP/Apache and MySQL are running
4. Access via `http://localhost/LGU2system/VDMsystem/`

## Default Credentials

- **Admin:** admin@vdm.gov.ph / admin123
- **Secretary:** secretary@vdm.gov.ph / secretary123
- **Councilor:** councilor@vdm.gov.ph / councilor123

## Directory Structure

```
VDMsystem/
├── database/           # SQL schemas and migrations
├── modules/
│   ├── core/          # Config, layouts, middleware, utilities
│   ├── authentication/ # Login, logout, register
│   ├── dashboard/     # Main dashboard
│   ├── documents/     # Document management
│   ├── voting/        # Voting sessions
│   ├── workflows/     # Routing and workflows
│   ├── reports/       # Reports and analytics
│   └── audit/         # Audit logs
├── public/
│   └── assets/        # CSS, JS, images
└── storage/           # Uploaded files, temp, logs
```

## License

© 2025 City Government of Valenzuela. All rights reserved.
