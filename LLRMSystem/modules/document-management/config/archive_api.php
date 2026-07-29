<?php
/**
 * Legislative Archive API Configuration
 *
 * Defines the API endpoints and authentication settings for external
 * systems to interact with the Legislative Archive via RESTful API.
 *
 * Authentication:
 *   X-API-Key header or Authorization: Bearer <key>
 *
 * Permissions (stored in integration_api_keys.permissions JSON):
 *   - archive_read    : List, search, get document details, download
 *   - archive_write   : Create, update documents
 *   - archive_delete  : Delete documents
 *   - all             : Full access
 *
 * Endpoints (all under /modules/document-management/api/archive.php):
 *   GET    ?action=list           List documents (paginated, filterable)
 *   GET    ?action=get&id={id}    Get single document with details
 *   GET    ?action=search&q={query} Search documents
 *   GET    ?action=download&id={id} Download document file
 *   GET    ?action=stats           Get archive statistics
 *   GET    ?action=types           List available document types
 *   POST   ?action=create          Create/upload a document
 *   PUT    ?action=update&id={id}  Update document metadata
 *   DELETE ?action=delete&id={id}  Soft-delete a document
 */

return [
    'api_name'          => 'legislative_archive',
    'api_version'      => 'v1',
    'base_url'          => 'https://llrm.spvalenzuela.com/modules/document-management/api/archive.php',
    'default_per_page'  => 20,
    'max_per_page'      => 100,
    'allowed_file_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif'],
    'max_file_size'     => 52428800, // 50MB
    'permissions'       => ['archive_read', 'archive_write', 'archive_delete', 'all'],
];
