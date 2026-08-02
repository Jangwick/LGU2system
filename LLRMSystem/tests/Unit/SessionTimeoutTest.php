<?php

use PHPUnit\Framework\TestCase;

class SessionTimeoutTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testNoUserIdReturnsEarly()
    {
        $middleware = new SessionTimeoutMiddleware();
        $_SESSION = [];

        $this->assertNull($middleware->checkSessionTimeout());
        $this->assertArrayNotHasKey('last_activity', $_SESSION);
    }

    public function testWithinTimeoutUpdatesLastActivity()
    {
        $middleware = new SessionTimeoutMiddleware();
        $_SESSION['user_id'] = 1;
        $_SESSION['last_activity'] = time();

        $middleware->checkSessionTimeout();

        $this->assertArrayHasKey('last_activity', $_SESSION);
        $this->assertArrayHasKey('session_timeout_remaining', $_SESSION);
        $this->assertGreaterThan(0, $_SESSION['session_timeout_remaining']);
    }

    public function testGetRemainingTimeCalculatesCorrectly()
    {
        $middleware = new SessionTimeoutMiddleware();
        $_SESSION['user_id'] = 1;
        $_SESSION['last_activity'] = time();

        $remaining = $middleware->getRemainingTime();
        $this->assertGreaterThan(0, $remaining);
        $this->assertLessThanOrEqual(300, $remaining);
    }

    public function testGetRemainingTimeReturnsZeroWhenNotLoggedIn()
    {
        $middleware = new SessionTimeoutMiddleware();
        $_SESSION = [];

        $this->assertSame(0, $middleware->getRemainingTime());
    }

    public function testGetRemainingTimeReturnsZeroWhenExpired()
    {
        $middleware = new SessionTimeoutMiddleware();
        $_SESSION['user_id'] = 1;
        $_SESSION['last_activity'] = time() - 500;

        if (!defined('SESSION_TIMEOUT_MINUTES')) {
            define('SESSION_TIMEOUT_MINUTES', 5);
        }

        $this->assertSame(0, $middleware->getRemainingTime());
    }
}
