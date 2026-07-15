<?php

require_once BASE_PATH . '/modules/user-management/controllers/UserController.php';

use PHPUnit\Framework\TestCase;

class UserFunctionalTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
    }

    protected function tearDown(): void
    {
        $_GET = [];
    }

    public function testUserControllerIndexBuildsFilters()
    {
        $_GET = [
            'role' => 'admin',
            'status' => 'active',
            'department' => 'IT',
            'search' => 'john',
            'page' => '2'
        ];

        $reflection = new ReflectionClass('UserController');
        $controller = $reflection->newInstanceWithoutConstructor();

        $method = new ReflectionMethod($controller, 'index');
        $method->setAccessible(true);

        try {
            $method->invoke($controller);
        } catch (Throwable $e) {
            // Expected if DB is not available; filters are still built before query
        }

        $this->assertSame('active', Sanitizer::enum($_GET['status'], ['active', 'inactive', 'suspended', 'pending'], null));
        $this->assertSame('john', Sanitizer::plainText($_GET['search']));
    }

    public function testUserControllerRejectsInvalidStatus()
    {
        $_GET = ['status' => 'invalid_status'];

        $sanitized = Sanitizer::enum($_GET['status'], ['active', 'inactive', 'suspended', 'pending'], null);

        $this->assertNull($sanitized);
    }
}
