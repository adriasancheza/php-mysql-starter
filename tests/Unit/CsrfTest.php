<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Csrf;
use PHPUnit\Framework\TestCase;

final class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        // Csrf reads/writes $_SESSION directly without requiring a real
        // PHP session to be started, which keeps this test fast and
        // isolated.
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testTokenIsGeneratedAndStable(): void
    {
        $first = Csrf::token();
        $second = Csrf::token();

        self::assertSame($first, $second);
        self::assertSame(64, strlen($first)); // 32 bytes, hex-encoded
    }

    public function testVerifyAcceptsMatchingToken(): void
    {
        $token = Csrf::token();

        self::assertTrue(Csrf::verify($token));
    }

    public function testVerifyRejectsWrongToken(): void
    {
        Csrf::token();

        self::assertFalse(Csrf::verify('not-the-right-token'));
    }

    public function testVerifyRejectsNullToken(): void
    {
        Csrf::token();

        self::assertFalse(Csrf::verify(null));
    }

    public function testVerifyRejectsWhenNoTokenInSession(): void
    {
        self::assertFalse(Csrf::verify('anything'));
    }

    public function testFieldContainsHiddenInputWithToken(): void
    {
        $field = Csrf::field();

        self::assertStringContainsString('type="hidden"', $field);
        self::assertStringContainsString('name="_csrf"', $field);
        self::assertStringContainsString(Csrf::token(), $field);
    }
}
