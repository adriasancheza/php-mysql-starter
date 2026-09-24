<?php

declare(strict_types=1);

namespace App\Auth;

use PDO;

/**
 * Very small DB-backed rate limiter for login attempts, keyed by email and
 * IP address. Not meant to replace a proper WAF, just a sane default.
 */
final class RateLimiter
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly int $maxAttempts = 5,
        private readonly int $windowSeconds = 900,
    ) {
    }

    public function tooManyAttempts(string $email, string $ipAddress): bool
    {
        return $this->countRecentAttempts($email, $ipAddress) >= $this->maxAttempts;
    }

    public function countRecentAttempts(string $email, string $ipAddress): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE (email = :email OR ip_address = :ip)
             AND attempted_at >= (NOW() - INTERVAL :window SECOND)'
        );
        $stmt->bindValue('email', $email);
        $stmt->bindValue('ip', $ipAddress);
        $stmt->bindValue('window', $this->windowSeconds, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function recordAttempt(string $email, string $ipAddress): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO login_attempts (email, ip_address) VALUES (:email, :ip)'
        );
        $stmt->execute(['email' => $email, 'ip' => $ipAddress]);
    }

    public function clearAttempts(string $email, string $ipAddress): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM login_attempts WHERE email = :email OR ip_address = :ip'
        );
        $stmt->execute(['email' => $email, 'ip' => $ipAddress]);
    }
}
