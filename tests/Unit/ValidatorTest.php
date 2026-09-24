<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testRequiredFailsOnEmptyValue(): void
    {
        $validator = new Validator(['name' => ''], ['name' => ['required']]);

        self::assertTrue($validator->fails());
        self::assertArrayHasKey('name', $validator->errors());
    }

    public function testRequiredFailsOnMissingKey(): void
    {
        $validator = new Validator([], ['name' => ['required']]);

        self::assertTrue($validator->fails());
    }

    public function testRequiredPassesWithValue(): void
    {
        $validator = new Validator(['name' => 'Adria'], ['name' => ['required']]);

        self::assertFalse($validator->fails());
    }

    public function testEmailRuleRejectsInvalidAddress(): void
    {
        $validator = new Validator(['email' => 'not-an-email'], ['email' => ['email']]);

        self::assertTrue($validator->fails());
    }

    public function testEmailRuleAcceptsValidAddress(): void
    {
        $validator = new Validator(['email' => 'user@example.com'], ['email' => ['email']]);

        self::assertFalse($validator->fails());
    }

    public function testMinLengthRule(): void
    {
        $validator = new Validator(['password' => 'short'], ['password' => ['min:8']]);

        self::assertTrue($validator->fails());
        self::assertSame(true, str_contains($validator->errors()['password'], 'at least 8'));
    }

    public function testMaxLengthRule(): void
    {
        $validator = new Validator(['title' => str_repeat('a', 200)], ['title' => ['max:191']]);

        self::assertTrue($validator->fails());
    }

    public function testConfirmedRuleRequiresMatchingField(): void
    {
        $validator = new Validator(
            ['password' => 'secret123', 'password_confirmation' => 'different'],
            ['password' => ['confirmed']],
        );

        self::assertTrue($validator->fails());
    }

    public function testConfirmedRulePassesWhenMatching(): void
    {
        $validator = new Validator(
            ['password' => 'secret123', 'password_confirmation' => 'secret123'],
            ['password' => ['confirmed']],
        );

        self::assertFalse($validator->fails());
    }

    public function testMultipleRulesOnSameField(): void
    {
        $validator = new Validator(['email' => ''], ['email' => ['required', 'email']]);

        self::assertTrue($validator->fails());
        self::assertCount(1, $validator->errors());
    }

    public function testValidateReturnsBool(): void
    {
        $validator = new Validator(['name' => 'ok'], ['name' => ['required']]);

        self::assertTrue($validator->validate());
    }
}
