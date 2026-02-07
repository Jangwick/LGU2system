# Database Entity Relationship Diagram (ERD)

This document provides the ERD for the Legislative Local Records Management System (LLRMSystem) database (`lrms_db`), visualized using [Mermaid.js](https://mermaid.js.org/).

## ERD Diagram

```mermaid
erDiagram
    USERS ||--o{ LEGISLATIVE_DOCUMENTS : "uploads"
    USERS ||--o{ ACTIVITY_LOGS : "performs"
    USERS ||--o{ NOTIFICATIONS : "receives"
    USERS ||--o{ DOCUMENT_VERSIONS : "creates"
    USERS ||--o| USER_PREFERENCES : "has"

    LEGISLATIVE_DOCUMENTS ||--o{ DOCUMENT_LINKS : "links_from"
    LEGISLATIVE_DOCUMENTS ||--o{ DOCUMENT_LINKS : "links_to"
    LEGISLATIVE_DOCUMENTS ||--o{ DOCUMENT_TAG_RELATIONSHIPS : "has"
    LEGISLATIVE_DOCUMENTS ||--o{ DOCUMENT_VERSIONS : "has"

    DOCUMENT_TAGS ||--o{ DOCUMENT_TAG_RELATIONSHIPS : "tagged_in"

    USERS {
        int id PK
        string full_name
        string email
        string username
        string password_hash
        string role
        string status
        string department
        string designation
        string phone
        string profile_picture
        datetime last_login
        datetime created_at
        datetime updated_at
    }

    LEGISLATIVE_DOCUMENTS {
        int id PK
        string reference_number
        string title
        string document_type
        date document_date
        string status
        string file_path
        string file_name
        int file_size
        string file_type
        text description
        string tags
        string source_module
        int source_id
        int uploaded_by FK
        datetime created_at
        datetime deleted_at
    }

    ACTIVITY_LOGS {
        int id PK
        int user_id FK
        string action
        string table_name
        int record_id
        text old_values
        text new_values
        string ip_address
        string user_agent
        string description
        datetime created_at
    }

    NOTIFICATIONS {
        int id PK
        int user_id FK "nullable"
        string type
        string title
        text message
        string source_module
        int source_id
        string priority
        json data
        datetime expires_at
        boolean is_read
        datetime read_at
        datetime created_at
    }

    DOCUMENT_LINKS {
        int id PK
        int document_id FK
        int linked_document_id FK
        string link_type
        datetime created_at
    }

    DOCUMENT_TAGS {
        int id PK
        string name
        string slug
        datetime created_at
    }

    DOCUMENT_TAG_RELATIONSHIPS {
        int document_id PK, FK
        int tag_id PK, FK
    }

    DOCUMENT_VERSIONS {
        int id PK
        int document_id FK
        int version_number
        string file_path
        string file_name
        int file_size
        text change_description
        int created_by FK
        datetime created_at
    }

    USER_PREFERENCES {
        int id PK
        int user_id FK
        boolean email_notifications
        string theme
        string language
        datetime created_at
        datetime updated_at
    }
```

## Relationships Description

- **Users**: Central entity. Users upload documents, perform actions (logged), receive notifications, and create document versions.
- **Legislative Documents**: Main content entity. Documents can be linked to other documents (relationships like "supersedes" or "amends"), can have multiple tags, and maintain a version history.
- **Activity Logs**: Tracks every major action performed by users for audit purposes.
- **Notifications**: System-wide and per-user alerts. NULL `user_id` represents a broadcast notification.
- **Versions**: Each time a document is updated, a new entry is created here to maintain history.
- **Tags**: Categorization system with a many-to-many relationship to documents.
