<?php

declare(strict_types=1);

namespace App\Auth;

use App\Core\Session;

/**
 * Coordinates registration/login/logout: hashing, session state, and
 * delegating rate-limit checks to RateLimiter.
 */
final class AuthService
{
    private const SESSION_USER_ID = '_auth_user_id';

    public function __construct(
        private readonly UserRepository $users,
        private readonly RateLimiter $rateLimiter,
    ) {
    }

    public function register(string $name, string $email, string $password): User
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $user = $this->users->create($name, $email, $hash);

        $this->login($user);

        return $user;
    }

    /**
     * @return User|string User on success, or an error string on failure.
     */
    public function attemptLogin(string $email, string $password, string $ipAddress): User|string
    {
        if ($this->rateLimiter->tooManyAttempts($email, $ipAddress)) {
            return 'Too many login attempts. Please try again later.';
        }

        $row = $this->users->findAuthRowByEmail($email);

        if ($row === null || !password_verify($password, $row['password_hash'])) {
            $this->rateLimiter->recordAttempt($email, $ipAddress);

            return 'Invalid email or password.';
        }

        $this->rateLimiter->clearAttempts($email, $ipAddress);

        $user = new User($row['id'], $row['name'], $row['email']);
        $this->login($user);

        return $user;
    }

    public function login(User $user): void
    {
        // Regenerate the session id on privilege change to prevent fixation.
        Session::regenerate();
        Session::put(self::SESSION_USER_ID, $user->id);
    }

    public function logout(): void
    {
        Session::forget(self::SESSION_USER_ID);
        Session::regenerate();
    }

    public function currentUser(): ?User
    {
        $id = Session::get(self::SESSION_USER_ID);

        if (!is_int($id)) {
            return null;
        }

        return $this->users->findById($id);
    }

    public function check(): bool
    {
        return Session::has(self::SESSION_USER_ID);
    }
}
