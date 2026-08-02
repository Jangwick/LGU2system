<?php
/**
 * Default cURL-based WebhookClient implementation.
 */
class CurlWebhookClient implements WebhookClient
{
    /**
     * @inheritdoc
     */
    public function send($url, $payload, $headers)
    {
        if (!function_exists('curl_init')) {
            return [
                'success' => false,
                'http_code' => 0,
                'response' => '',
                'error' => 'cURL extension not available'
            ];
        }

        $ch = curl_init($url);

        $curlHeaders = [];
        foreach ($headers as $key => $value) {
            $curlHeaders[] = "{$key}: {$value}";
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $response !== false ? $response : '',
            'error' => $error ?: null
        ];
    }
}
