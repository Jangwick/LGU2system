# System Architecture Defense Document

## Executive Summary

The Legislative Records Management System (LRMS) is a modular, monolithic web application built on PHP using the Model-View-Controller (MVC) architectural pattern. The system is designed to manage legislative documents, track their lifecycle, and provide comprehensive search and analytics capabilities.

## Architecture Overview

### Architectural Pattern: Modular MVC (Monolithic)

**Decision:** The LRMS uses a modular MVC pattern within a monolithic application structure rather than a microservices architecture.

**Justification:**

1. **Team Size and Complexity:** The development team is small-to-medium sized. Microservices introduce operational complexity (service discovery, inter-service communication, distributed transactions, monitoring) that outweighs benefits for this scale.

2. **Database Constraints:** The system uses a single MySQL database. Microservices typically require separate databases per service to achieve true independence. A shared database would couple services anyway, negating microservice benefits.

3. **Deployment Simplicity:** Monolithic deployment is simpler for the current infrastructure (XAMPP/WAMP stack). No need for container orchestration (Kubernetes) or complex CI/CD pipelines for multiple services.

4. **Development Velocity:** With a monolithic structure, developers can work on features end-to-end without coordinating across service boundaries. This is crucial for a capstone project with limited time.

5. **Cost Efficiency:** Single server deployment reduces infrastructure costs compared to running multiple service instances.

6. **Performance:** For the expected user load (LGU internal users), a single application server is sufficient. Microservices introduce network latency between services.

### Modular Structure

While monolithic, the system is organized into logical modules:

```
modules/
├── authentication/        # Login, OTP, password management
├── document-management/    # CRUD, versioning, linking
├── user-management/        # User accounts, roles, permissions
├── reports-analytics/      # Dashboard, statistics, exports
├── search/                 # Keyword and semantic search
├── audit/                  # Activity logging
├── notifications/          # User notifications
├── help/                   # Documentation and support
└── research-analysis/      # Cross-reference, comparison tools

core/
├── config/                 # Configuration files
├── middleware/             # Permission, CSRF, session management
├── utils/                  # Helpers, validators, encryption
└── layouts/                # Shared UI components
```

**Benefits of Modular Monolith:**

- **Clear Boundaries:** Each module has its own models, views, controllers, and services
- **Independent Development:** Teams can work on different modules with minimal conflict
- **Future Migration:** Modules can be extracted into microservices if needed later
- **Maintainability:** Related code is co-located, making it easier to understand and modify

## Technology Stack

### Backend
- **Language:** PHP 8.0+
- **Database:** MySQL 8.0
- **Architecture:** MVC with Service Layer

### Frontend
- **Framework:** Vanilla PHP with Tailwind CSS 4.0
- **Charts:** Chart.js for analytics visualization
- **Icons:** Bootstrap Icons
- **JavaScript:** ES6+ with fetch API

### Security
- **Authentication:** Two-factor with OTP (1-minute expiry)
- **Authorization:** Role-Based Access Control (RBAC)
- **Session Management:** 2-minute auto-logout
- **Encryption:** AES-256 for confidential documents
- **CSRF Protection:** Token-based CSRF middleware
- **SQL Injection Prevention:** Prepared statements (PDO)

## Data Flow

### Document Upload Flow
```
1. User submits document form → DocumentController
2. Validator validates input → DocumentService
3. FileStorageService uploads file → File system
4. DocumentModel saves metadata → Database
5. EncryptionService encrypts (if confidential) → File system
6. Logger logs activity → Activity logs
7. NotificationService notifies users → Notifications
```

### Search Flow
```
1. User enters query → SearchController
2. SearchService performs keyword search → Database
3. EmbeddingService generates query embedding → AI Service
4. Semantic search via cosine similarity → Database embeddings
5. Results merged using Reciprocal Rank Fusion → Ranked results
6. Results displayed → View
```

## Security Architecture

### Authentication Layer
- **Login Flow:** Email/Password → OTP verification → Session creation
- **OTP Expiry:** 1 minute (reduced from 10 for security)
- **Session Timeout:** 2 minutes of inactivity
- **Password Recovery:** Disabled for Admin/Super Admin accounts

### Authorization Layer
```
Role Hierarchy (higher = more permissions):
1. viewer (1)
2. staff (2)
3. officer (3)
4. administrator (4)
5. super_admin (5)
```

### Data Protection
- **Soft Delete:** No direct record deletion, maintains audit trail
- **Confidentiality Levels:** public, internal, confidential, restricted
- **Document Encryption:** AES-256 for confidential documents
- **Blur Effect:** Confidential documents appear blurred in preview
- **Password Prompt:** Re-authentication required to access confidential docs

## Scalability Considerations

### Current Capacity
- Single application server handles expected LGU user load
- MySQL database with proper indexing
- File storage on local filesystem

### Future Scaling Options
1. **Vertical Scaling:** Add CPU/RAM to existing server
2. **Horizontal Scaling:** Load balancer + multiple application instances
3. **Database Scaling:** Read replicas for analytics queries
4. **Module Extraction:** Extract high-traffic modules (search, documents) into microservices
5. **CDN:** Static asset delivery via CDN
6. **Object Storage:** Move file storage to AWS S3 or similar

## Performance Optimizations

### Database
- **Indexing:** Strategic indexes on frequently queried columns
- **Prepared Statements:** Reuse query execution plans
- **Query Optimization:** LIMIT clauses, proper JOINs

### Caching Strategy
- **Session Data:** In-memory (PHP sessions)
- **File System:** Direct filesystem access (no caching layer currently)
- **Future:** Redis for session storage and query caching

### Frontend
- **Lazy Loading:** Components load on demand
- **Minification:** CSS/JS minification in production
- **CDN Ready:** Tailwind CSS via CDN

## Reliability and Availability

### Error Handling
- **Try-Catch Blocks:** Graceful error handling throughout
- **Logging:** Comprehensive activity and error logging
- **User Feedback:** Clear error messages for users

### Backup Strategy
- **Database Backups:** Super Admin can trigger backups
- **File Backups:** Regular filesystem backups
- **Version Control:** Git for code management

## Monitoring and Observability

### Current Implementation
- **Activity Logs:** All user actions logged
- **Audit Trail:** Document lifecycle tracking
- **Error Logging:** PHP error logs

### Future Enhancements
- **Application Performance Monitoring (APM):** New Relic, Datadog
- **Log Aggregation:** ELK Stack or similar
- **Health Checks:** Endpoint for uptime monitoring

## Compliance

### R.A. 7160 (Local Government Code)
- **Document Types:** Aligned with legislative document categories
- **No "Others" Category:** All documents properly classified
- **Naming Conventions:** Standardized reference numbers (TYPE-YYYY-NNN)

### Data Privacy
- **Confidentiality Levels:** Four-tier classification system
- **Access Control:** Role-based document access
- **Audit Trail:** Complete transaction logging (who, what, when)

## Conclusion

The LRMS architecture balances simplicity with scalability. The modular monolithic approach provides clear code organization while avoiding the operational overhead of microservices. Security is prioritized with strict session policies, encryption, and comprehensive access controls. The system is designed to evolve - modules can be extracted into microservices if future scaling demands it.

### Key Strengths
1. **Maintainability:** Clear modular structure
2. **Security:** Multi-layered protection
3. **Performance:** Optimized for current load
4. **Compliance:** Meets R.A. 7160 requirements
5. **Scalability:** Path to horizontal scaling available

### Trade-offs Accepted
1. **Single Database:** Limits true service independence
2. **No Caching Layer:** Simplicity over performance optimization
3. **Manual Deployment:** No automated CI/CD (acceptable for current scale)

The architecture is appropriate for the current requirements and provides a solid foundation for future growth.
