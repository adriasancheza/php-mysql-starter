<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Env;
use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        Env::reset();
        $this->tempFile = tempnam(sys_get_temp_dir(), 'envtest');
    }

    protected function tearDown(): void
    {
        Env::reset();
        if (is_file($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testLoadsSimpleKeyValuePairs(): void
    {
        file_put_contents($this->tempFile, "FOO=bar\nBAZ=qux\n");

        Env::load($this->tempFile);

        self::assertSame('bar', Env::get('FOO'));
        self::assertSame('qux', Env::get('BAZ'));
    }

    public function testIgnoresCommentsAndBlankLines(): void
    {
        file_put_contents($this->tempFile, "# a comment\n\nFOO=bar\n   \n# another\n");

        Env::load($this->tempFile);

        self::assertSame('bar', Env::get('FOO'));
    }

    public function testStripsSurroundingQuotes(): void
    {
        file_put_contents($this->tempFile, "FOO=\"bar baz\"\nSINGLE='qux'\n");

        Env::load($this->tempFile);

        self::assertSame('bar baz', Env::get('FOO'));
        self::assertSame('qux', Env::get('SINGLE'));
    }

    public function testReturnsDefaultWhenKeyMissing(): void
    {
        Env::load($this->tempFile);

        self::assertSame('fallback', Env::get('MISSING_KEY', 'fallback'));
        self::assertNull(Env::get('MISSING_KEY'));
    }

    public function testGetBoolParsesTruthyStrings(): void
    {
        file_put_contents($this->tempFile, "A=true\nB=1\nC=false\nD=0\n");

        Env::load($this->tempFile);

        self::assertTrue(Env::getBool('A'));
        self::assertTrue(Env::getBool('B'));
        self::assertFalse(Env::getBool('C'));
        self::assertFalse(Env::getBool('D'));
        self::assertFalse(Env::getBool('MISSING', false));
    }

    public function testGetIntParsesNumericStrings(): void
    {
        file_put_contents($this->tempFile, "PORT=3306\nNOT_NUM=abc\n");

        Env::load($this->tempFile);

        self::assertSame(3306, Env::getInt('PORT'));
        self::assertSame(0, Env::getInt('NOT_NUM'));
        self::assertSame(42, Env::getInt('MISSING', 42));
    }

    public function testMissingFileDoesNotThrow(): void
    {
        Env::load($this->tempFile . '-does-not-exist');

        self::assertNull(Env::get('ANYTHING'));
    }
}
