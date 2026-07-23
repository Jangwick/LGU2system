<?php
/**
 * PCMS integration configuration
 * Supports: consultation_summary
 */
return [
    'source_system'    => 'pcms',
    'module_type'      => 'consultation_summaries',
    'document_type'    => 'consultation_summary',
    'base_url'         => 'https://consultation.spvalenzuela.com/api/v1',
    'bearer_token'     => 'pcms_live_5a9c3e7f1b6048d2e6a8c4f9b1d70328',
    'list_endpoint'    => 'documents.php', // adjust when actual list endpoint is known
    'query_param'      => 'since',
    'lrms_receive_url' => 'https://llrm.spvalenzuela.com/modules/integration/api/receive_document.php',
    'lrms_module_name' => 'pcms',
];
