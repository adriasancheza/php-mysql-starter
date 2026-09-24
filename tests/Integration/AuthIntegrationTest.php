<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Auth\AuthService;
use App\Auth\RateLimiter;
use App\Auth\UserRepository;

final class AuthIntegrationTest extends DatabaseTestCase
{
    private AuthService $auth;
    private UserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();

        if (!isset($this->pdo)) {
            return; // test was skipped
        }

        $_SESSION = [];
        $this->users = new UserRepository($this->pdo);
        $this->auth = new AuthService($this->users, new RateLimiter($this->pdo));
    }

    public function testRegisterCreatesUserAndLogsIn(): void
    {
        $user = $this->auth->register('Ada Lovelace', 'ada@example.com', 'super-secret-1');

        self::assertSame('ada@example.com', $user->email);
        self::assertTrue($this->auth->check());
        self::assertTrue($this->users->emailExists('ada@example.com'));
    }

    public function testPasswordIsHashedNotStoredInPlainText(): void
    {
        $this->auth->register('Ada Lovelace', 'ada@example.com', 'super-secret-1');

        $row = $this->users->findAuthRowByEmail('ada@example.com');

        self::assertNotNull($row);
        self::assertNotSame('super-secret-1', $row['password_hash']);
        self::assertTrue(password_verify('super-secret-1', $row['password_hash']));
    }

    public function testAttemptLoginSucceedsWithCorrectCredentials(): void
    {
        $this->auth->register('Ada Lovelace', 'ada@example.com', 'super-secret-1');
        $this->auth->logout();

        $result = $this->auth->attemptLogin('ada@example.com', 'super-secret-1', '127.0.0.1');

        self::assertInstanceOf(\App\Auth\User::class, $result);
        self::assertTrue($this->auth->check());
    }

    public function testAttemptLoginFailsWithWrongPassword(): void
    {
        $this->auth->register('Ada Lovelace', 'ada@example.com', 'super-secret-1');
        $this->auth->logout();

        $result = $this->auth->attemptLogin('ada@example.com', 'wrong-password', '127.0.0.1');

        self::assertIsString($result);
    }

    public function testRateLimiterBlocksAfterTooManyFailedAttempts(): void
    {
        $this->auth->register('Ada Lovelace', 'ada@example.com', 'super-secret-1');
        $this->auth->logout();

        for ($i = 0; $i < 5; $i++) {
            $this->auth->attemptLogin('ada@example.com', 'wrong-password', '10.0.0.5');
        }

        $result = $this->auth->attemptLogin('ada@example.com', 'super-secret-1', '10.0.0.5');

        self::assertIsString($result);
        self::assertStringContainsString('Too many', $result);
    }

    public function testLogoutClearsSession(): void
    {
        $this->auth->register('Ada Lovelace', 'ada@example.com', 'super-secret-1');

        self::assertTrue($this->auth->check());

        $this->auth->logout();

        self::assertFalse($this->auth->check());
        self::assertNull($this->auth->currentUser());
    }
}
