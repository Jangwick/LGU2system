<?php

use PHPUnit\Framework\TestCase;

class SanitizerTest extends TestCase
{
    public function testStringRemovesControlCharactersAndTrims()
    {
        $this->assertSame('hello', Sanitizer::string("  hello  \x00\x01\x02  "));
    }

    public function testStringHandlesNullAndBool()
    {
        $this->assertSame('', Sanitizer::string(null));
        $this->assertSame('', Sanitizer::string(true));
    }

    public function testStringMaxLength()
    {
        $this->assertSame('abc', Sanitizer::string('abcdef', ['maxLength' => 3]));
    }

    public function testPlainTextStripsTags()
    {
        $this->assertSame('alert(1)Hello world', Sanitizer::plainText('<script>alert(1)</script>Hello world'));
    }

    public function testPlainTextHandlesArrayInput()
    {
        $this->assertSame('', Sanitizer::plainText(['a', 'b']));
    }

    public function testRichTextRemovesDangerousContentButKeepsSafeHtml()
    {
        $input = '<p>Hello <b>world</b></p><script>alert(1)</script><a href="javascript:alert(1)">click</a><img src="data:text/html,test">';
        $output = Sanitizer::richText($input);
        $this->assertStringContainsString('<p>Hello <b>world</b></p>', $output);
        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringNotContainsString('javascript:', $output);
    }

    public function testRichTextRemovesEventHandlers()
    {
        $input = '<button onclick="alert(1)">click</button>';
        $output = Sanitizer::richText($input);
        $this->assertStringNotContainsString('onclick', $output);
    }

    public function testEmailSanitizesAndStripsInvalidChars()
    {
        $this->assertSame('test@example.com', Sanitizer::email('<test@example.com>'));
    }

    public function testIntReturnsDefaultForInvalidInput()
    {
        $this->assertSame(42, Sanitizer::int('42', 1));
        $this->assertSame(1, Sanitizer::int('abc', 1));
        $this->assertSame(1, Sanitizer::int(null, 1));
        $this->assertSame(12, Sanitizer::int('12.0', 1));
    }

    public function testFloatReturnsDefaultForInvalidInput()
    {
        $this->assertSame(3.14, Sanitizer::float('3.14', 0.0));
        $this->assertSame(0.0, Sanitizer::float('abc', 0.0));
    }

    public function testBoolRecognizesTruthyAndFalsyValues()
    {
        $this->assertTrue(Sanitizer::bool('yes'));
        $this->assertTrue(Sanitizer::bool('on'));
        $this->assertTrue(Sanitizer::bool('1'));
        $this->assertFalse(Sanitizer::bool('no'));
        $this->assertFalse(Sanitizer::bool('0'));
        $this->assertFalse(Sanitizer::bool(''));
    }

    public function testEnumWhitelistsValues()
    {
        $this->assertSame('admin', Sanitizer::enum('admin', ['admin', 'user'], 'viewer'));
        $this->assertSame('viewer', Sanitizer::enum('hacker', ['admin', 'user'], 'viewer'));
    }

    public function testFilenamePreventsPathTraversal()
    {
        $this->assertSame('file.txt', Sanitizer::filename('../../../etc/passwd/file.txt'));
        $this->assertSame('file.txt', Sanitizer::filename('..file.txt'));
    }

    public function testFilenameStripsDangerousCharacters()
    {
        $this->assertSame('my file.txt', Sanitizer::filename('my <file>.txt'));
    }

    public function testDateNormalizesToYmd()
    {
        $this->assertSame('2026-07-15', Sanitizer::date('2026-07-15', ''));
        $this->assertSame('2026-07-15', Sanitizer::date('July 15, 2026', ''));
        $this->assertSame('', Sanitizer::date('not-a-date', ''));
    }

    public function testArrayRecursivelySanitizes()
    {
        $input = ['<script>alert(1)</script>', 'safe', ['nested' => '<b>keep</b>']];
        $output = Sanitizer::array($input, 'plainText');
        $this->assertSame('alert(1)', $output[0]);
        $this->assertSame('safe', $output[1]);
        $this->assertSame('keep', $output[2]['nested']);
    }

    public function testForHtmlEscapesOutput()
    {
        $this->assertSame('&lt;script&gt;', Sanitizer::forHtml('<script>'));
        $this->assertSame('&#039;test&#039;', Sanitizer::forHtml("'test'"));
    }

    public function testApplyRuleDispatchesCorrectly()
    {
        $this->assertSame('hello', Sanitizer::applyRule('  hello  ', 'string'));
        $this->assertSame('hello', Sanitizer::applyRule('  hello  ', 'plain'));
        $this->assertSame('<p>Hello</p>', Sanitizer::applyRule('<p>Hello</p>', 'rich'));
        $this->assertSame('test@example.com', Sanitizer::applyRule('<test@example.com>', 'email'));
        $this->assertSame(42, Sanitizer::applyRule('42', 'int'));
        $this->assertSame(3.14, Sanitizer::applyRule('3.14', 'float'));
        $this->assertTrue(Sanitizer::applyRule('yes', 'bool'));
        $this->assertSame('file.txt', Sanitizer::applyRule('../../../file.txt', 'filename'));
        $this->assertSame('2026-07-15', Sanitizer::applyRule('July 15, 2026', 'date'));
        $this->assertSame('raw value', Sanitizer::applyRule('raw value', 'raw'));
        $this->assertSame('fallback', Sanitizer::applyRule('fallback', 'unknown'));
    }

    public function testFilenameMaxLength()
    {
        $long = str_repeat('a', 300) . '.pdf';
        $sanitized = Sanitizer::filename($long, 255);
        $this->assertLessThanOrEqual(255, mb_strlen($sanitized));
        $this->assertStringEndsWith('.pdf', $sanitized);
    }

    public function testDateReturnsDefault()
    {
        $this->assertSame('2026-01-01', Sanitizer::date('not-a-date', '2026-01-01'));
        $this->assertSame('2026-01-01', Sanitizer::date('', '2026-01-01'));
    }

    public function testBoolHandlesIntegersAndNull()
    {
        $this->assertTrue(Sanitizer::bool(1));
        $this->assertFalse(Sanitizer::bool(0));
        $this->assertFalse(Sanitizer::bool(null));
        $this->assertTrue(Sanitizer::bool('TRUE'));
    }

    public function testArrayWithRawRule()
    {
        $input = ['keep' => '<b>bold</b>'];
        $output = Sanitizer::array($input, 'raw');
        $this->assertSame('<b>bold</b>', $output['keep']);
    }
}
