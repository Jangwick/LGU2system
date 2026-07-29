<?php
/**
 * LAS (Legislative Archive System) integration configuration
 * Connects to the external Legislative Archive at las.spvalenzuela.com
 *
 * Supports: archive documents (ordinance, resolution, session, etc.)
 *
 * The LAS API is hosted on the LLRM system at:
 *   https://llrm.spvalenzuela.com/modules/document-management/api/archive.php
 *
 * Authentication: X-API-Key header
 * API Key: ar_c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8
 */
return [
    'source_system'    => 'las',
    'module_type'      => 'archives',
    'document_type'    => 'archive',
    'base_url'         => 'https://las.spvalenzuela.com/LGU2-Archives',
    'login_url'        => 'https://las.spvalenzuela.com/LGU2-Archives/login.php',
    'bearer_token'     => 'ar_c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8',
    'list_endpoint'    => 'archive.php?action=list',
    'get_endpoint'     => 'archive.php?action=get',
    'search_endpoint'  => 'archive.php?action=search',
    'create_endpoint'  => 'archive.php?action=create',
    'update_endpoint'  => 'archive.php?action=update',
    'delete_endpoint'  => 'archive.php?action=delete',
    'download_endpoint'=> 'archive.php?action=download',
    'stats_endpoint'   => 'archive.php?action=stats',
    'types_endpoint'   => 'archive.php?action=types',
    'lrms_receive_url' => 'https://llrm.spvalenzuela.com/modules/integration/api/receive_document.php',
    'lrms_module_name' => 'las',
];
