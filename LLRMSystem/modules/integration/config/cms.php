<?php
/**
 * CMS integration configuration
 * Supports: committee_report
 */
return [
    'source_system'    => 'cms',
    'module_type'      => 'committee_reports',
    'document_type'    => 'committee_report',
    'base_url'         => 'https://cms.spvalenzuela.com/api/v1',
    'bearer_token'     => 'cms_live_9c1e5a7b3f8042d6b8e2a4c7f1d90638',
    'list_endpoint'    => 'documents.php', // adjust when actual list endpoint is known
    'query_param'      => 'since',
    'lrms_receive_url' => 'https://llrm.spvalenzuela.com/modules/integration/api/receive_document.php',
    'lrms_module_name' => 'cms',
];
