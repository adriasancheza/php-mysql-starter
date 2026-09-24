<?php

declare(strict_types=1);

namespace App\Auth;

/**
 * Plain data object for a users row. Deliberately does not carry
 * password_hash outside of the repository layer that needs it.
 */
final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            name: (string) $row['name'],
            email: (string) $row['email'],
        );
    }
}
