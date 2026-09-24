<?php

declare(strict_types=1);

namespace App\Notes;

final class Note
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly string $title,
        public readonly string $body,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            userId: (int) $row['user_id'],
            title: (string) $row['title'],
            body: (string) $row['body'],
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at'],
        );
    }
}
