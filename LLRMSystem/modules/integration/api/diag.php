<?php
header('Content-Type: application/json');

echo json_encode([
    'method' => $_SERVER['REQUEST_METHOD'] ?? null,
    'get' => $_GET,
    'post' => $_POST,
    'files' => $_FILES,
    'server_auth' => [
        'HTTP_AUTHORIZATION' => $_SERVER['HTTP_AUTHORIZATION'] ?? null,
        'REDIRECT_HTTP_AUTHORIZATION' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null,
        'HTTP_X_API_KEY' => $_SERVER['HTTP_X_API_KEY'] ?? null,
        'REDIRECT_HTTP_X_API_KEY' => $_SERVER['REDIRECT_HTTP_X_API_KEY'] ?? null,
        'getenv_HTTP_AUTHORIZATION' => getenv('HTTP_AUTHORIZATION') ?: null,
        'getenv_HTTP_X_API_KEY' => getenv('HTTP_X_API_KEY') ?: null,
    ],
    'getallheaders' => function_exists('getallheaders') ? @getallheaders() : 'not available',
    'apache_request_headers' => function_exists('apache_request_headers') ? @apache_request_headers() : 'not available',
    'php_sapi' => php_sapi_name(),
]);
