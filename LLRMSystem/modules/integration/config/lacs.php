<?php
/**
 * LACS integration configuration
 * Adjust LACS_LIST_ENDPOINT to the actual LACS document-list path.
 */
return [
    'source_system'  => 'lacs',
    'module_type'    => 'agendas',
    'base_url'       => 'https://lacs.spvalenzuela.com/api/v1',
    'bearer_token'   => 'lacs_live_4e8b1d9f2a6c7053e9b4f8a1c6d20745',
    'list_endpoint'  => 'agendas.php', // e.g. agendas.php, documents.php, list.php
    'query_param'    => 'since',       // LACS query parameter for incremental polling
    'lrms_receive_url' => 'https://llrm.spvalenzuela.com/LLRMSystem/modules/integration/api/receive.php',
    'lrms_module_name' => 'lacs',
];
