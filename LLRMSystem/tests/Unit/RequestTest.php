<?php

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
        $_SERVER = [];
    }

    protected function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
        $_SERVER = [];
    }

    public function testGetSanitizesString()
    {
        $_GET['name'] = '<script>alert(1)</script>  John  ';
        $this->assertSame('alert(1)  John', Request::get('name', '', 'plainText'));
    }

    public function testGetReturnsDefaultForMissingKey()
    {
        $this->assertSame('default', Request::get('missing', 'default'));
    }

    public function testPostSanitizesInt()
    {
        $_POST['page'] = '42';
        $this->assertSame(42, Request::post('page', 1, 'int'));
    }

    public function testPostEnumValidation()
    {
        $_POST['role'] = 'admin';
        $this->assertSame('admin', Request::post('role', 'viewer', 'enum', ['admin', 'staff', 'viewer']));

        $_POST['role'] = 'hacker';
        $this->assertSame('viewer', Request::post('role', 'viewer', 'enum', ['admin', 'staff', 'viewer']));
    }

    public function testPostAllBulkSanitizes()
    {
        $_POST = [
            'name' => '  <b>John</b>  ',
            'age' => '25',
            'email' => 'john@example.com',
            'role' => 'superadmin'
        ];

        $result = Request::postAll([
            'name' => 'plainText',
            'age' => 'int',
            'email' => 'email',
            'role' => 'enum'
        ]);

        $this->assertSame('John', $result['name']);
        $this->assertSame(25, $result['age']);
        $this->assertSame('john@example.com', $result['email']);
        $this->assertSame('', $result['role']);
    }

    public function testMethodReturnsUppercaseRequestMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $this->assertSame('POST', Request::method());
    }

    public function testIsPost()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(Request::isPost());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertFalse(Request::isPost());
    }

    public function testIsAjax()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue(Request::isAjax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = '';
        $this->assertFalse(Request::isAjax());
    }
}
