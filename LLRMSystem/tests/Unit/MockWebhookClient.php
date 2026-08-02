<?php
/**
 * Mock WebhookClient for testing outbound webhooks without network calls.
 */
class MockWebhookClient implements WebhookClient
{
    private $calls = [];
    private $responses = [];
    private $defaultResponse;

    public function __construct($defaultResponse = null)
    {
        $this->defaultResponse = $defaultResponse ?? [
            'success' => true,
            'http_code' => 200,
            'response' => 'OK',
            'error' => null
        ];
    }

    public function send($url, $payload, $headers)
    {
        $this->calls[] = [
            'url' => $url,
            'payload' => $payload,
            'headers' => $headers
        ];

        $response = array_shift($this->responses) ?: $this->defaultResponse;
        return $response;
    }

    public function pushResponse($response)
    {
        $this->responses[] = $response;
    }

    public function getCalls()
    {
        return $this->calls;
    }

    public function getLastCall()
    {
        return $this->calls[count($this->calls) - 1] ?? null;
    }

    public function wasCalled()
    {
        return count($this->calls) > 0;
    }
}
