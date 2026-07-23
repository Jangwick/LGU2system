<?php
/**
 * PHMS integration configuration
 * Supports: public_notice, hearing_log
 */
return [
    'source_system'    => 'phms',
    'module_type'      => 'public_notices',
    'document_type'    => 'public_notice',
    'base_url'         => 'https://phms.spvalenzuela.com/api/v1',
    'bearer_token'     => 'phms_live_2d6f8a4c1e9057b3a9c5e7f2b4d80156',
    'list_endpoint'    => 'documents.php', // adjust when actual list endpoint is known
    'query_param'      => 'since',
    'lrms_receive_url' => 'https://llrm.spvalenzuela.com/LLRMSystem/modules/integration/api/receive_document.php',
    'lrms_module_name' => 'phms',
];
