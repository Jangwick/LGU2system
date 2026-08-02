<?php
/**
 * WebhookClient interface for outbound integration notifications.
 */
interface WebhookClient
{
    /**
     * Send a webhook payload to the given URL.
     *
     * @param string $url
     * @param string $payload JSON string
     * @param array  $headers Key/value pairs
     * @return array ['success' => bool, 'http_code' => int, 'response' => string, 'error' => string|null]
     */
    public function send($url, $payload, $headers);
}
