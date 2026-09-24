<?php

declare(strict_types=1);

namespace App\Auth;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row === false ? null : User::fromRow($row);
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : User::fromRow($row);
    }

    /**
     * @return array{id: int, name: string, email: string, password_hash: string}|null
     */
    public function findAuthRowByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'email' => (string) $row['email'],
            'password_hash' => (string) $row['password_hash'],
        ];
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);

        return $stmt->fetchColumn() !== false;
    }

    public function create(string $name, string $email, string $passwordHash): User
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)'
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return new User($id, $name, $email);
    }
}
