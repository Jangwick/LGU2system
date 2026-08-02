<?php

use PHPUnit\Framework\TestCase;

class ApiCacheMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/test?page=1';
        $_SERVER['QUERY_STRING'] = 'page=1';
        $_SESSION = [];

        // Reset internal state so tests are not affected by a previous start()
        $reflection = new ReflectionClass(ApiCacheMiddleware::class);
        $started = $reflection->getProperty('started');
        $started->setAccessible(true);
        $started->setValue(null, false);

        $cacheKey = $reflection->getProperty('cacheKey');
        $cacheKey->setAccessible(true);
        $cacheKey->setValue(null, '');

        $cacheable = $reflection->getProperty('cacheable');
        $cacheable->setAccessible(true);
        $cacheable->setValue(null, false);

        // Disable the live CompressionMiddleware so ApiCacheMiddleware tests
        // can safely use their own output buffer.
        $cReflection = new ReflectionClass(CompressionMiddleware::class);
        $cStarted = $cReflection->getProperty('started');
        $cStarted->setAccessible(true);
        $cStarted->setValue(null, false);
    }

    protected function tearDown(): void
    {
        CacheService::flush();
        $_SERVER = [];
        $_SESSION = [];
    }

    public function test_build_key_changes_with_user_session(): void
    {
        $method = new ReflectionMethod(ApiCacheMiddleware::class, 'buildKey');
        $method->setAccessible(true);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $key1 = $method->invoke(null, '/api/test');

        session_start();
        $_SESSION['user_id'] = 5;
        $key2 = $method->invoke(null, '/api/test');

        $this->assertNotSame($key1, $key2);
        session_destroy();
    }

    public function test_is_api_request_detects_api_path(): void
    {
        $method = new ReflectionMethod(ApiCacheMiddleware::class, 'isApiRequest');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke(null, '/api/documents'));
        $this->assertTrue($method->invoke(null, '\\api\\documents'));
        $this->assertFalse($method->invoke(null, '/admin/dashboard'));
    }

    public function test_is_cacheable_response_only_accepts_json(): void
    {
        $method = new ReflectionMethod(ApiCacheMiddleware::class, 'isCacheableResponse');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke(null, '{"success":true}'));
        $this->assertTrue($method->invoke(null, '[1,2,3]'));
        $this->assertFalse($method->invoke(null, '{"success":false}'));
        $this->assertFalse($method->invoke(null, '<html></html>'));
        $this->assertFalse($method->invoke(null, '{ invalid json'));
    }

    public function test_end_caches_json_response(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/unit';

        $reflection = new ReflectionClass(ApiCacheMiddleware::class);

        $cacheable = $reflection->getProperty('cacheable');
        $cacheable->setAccessible(true);
        $cacheable->setValue(null, true);

        $cacheKey = $reflection->getProperty('cacheKey');
        $cacheKey->setAccessible(true);
        $cacheKey->setValue(null, 'api:cache:GET:unit-test');

        ob_start();
        echo '{"success":true,"data":1}';
        ApiCacheMiddleware::end();

        $cached = CacheService::get('api:cache:GET:unit-test');
        $this->assertSame('{"success":true,"data":1}', $cached);
    }

    public function test_start_ignores_non_api_request(): void
    {
        $reflection = new ReflectionClass(ApiCacheMiddleware::class);
        $cacheable = $reflection->getProperty('cacheable');
        $cacheable->setAccessible(true);
        $cacheable->setValue(null, false);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/admin/dashboard';

        ApiCacheMiddleware::start();

        $this->assertFalse($cacheable->getValue());
    }

    public function test_start_ignores_non_get_request(): void
    {
        $reflection = new ReflectionClass(ApiCacheMiddleware::class);
        $cacheable = $reflection->getProperty('cacheable');
        $cacheable->setAccessible(true);
        $cacheable->setValue(null, false);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/api/documents';

        ApiCacheMiddleware::start();

        $this->assertFalse($cacheable->getValue());
    }

    public function test_get_request_uri_includes_query_string(): void
    {
        $method = new ReflectionMethod(ApiCacheMiddleware::class, 'getRequestUri');
        $method->setAccessible(true);

        $_SERVER['REQUEST_URI'] = '/api/search';
        $_SERVER['QUERY_STRING'] = 'q=budget';

        $this->assertSame('/api/search?q=budget', $method->invoke(null));
    }
}
