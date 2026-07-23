<?php
/**
 * ORTS integration configuration
 * Supports: ordinance, resolution
 */
return [
    'source_system'    => 'orts',
    'module_type'      => 'ordinances',
    'document_type'    => 'ordinance',
    'base_url'         => 'https://ort.spvalenzuela.com/api/v1',
    'bearer_token'     => 'ort_live_7f3a9c2e1b5840d6a8e4f1c9b7d50362',
    'list_endpoint'    => 'documents.php', // adjust when actual list endpoint is known
    'query_param'      => 'since',
    'lrms_receive_url' => 'https://llrm.spvalenzuela.com/LLRMSystem/modules/integration/api/receive_document.php',
    'lrms_module_name' => 'orts',
];
