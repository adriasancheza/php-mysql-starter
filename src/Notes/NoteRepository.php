<?php

declare(strict_types=1);

namespace App\Notes;

use PDO;

/**
 * All queries are scoped by user_id so one user can never read or modify
 * another user's notes, even if an id is guessed or tampered with.
 */
final class NoteRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return list<Note>
     */
    public function allForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, user_id, title, body, created_at, updated_at
             FROM notes WHERE user_id = :user_id ORDER BY created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return array_map(Note::fromRow(...), $stmt->fetchAll());
    }

    public function findForUser(int $id, int $userId): ?Note
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, user_id, title, body, created_at, updated_at
             FROM notes WHERE id = :id AND user_id = :user_id LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $row = $stmt->fetch();

        return $row === false ? null : Note::fromRow($row);
    }

    public function create(int $userId, string $title, string $body): Note
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO notes (user_id, title, body) VALUES (:user_id, :title, :body)'
        );
        $stmt->execute(['user_id' => $userId, 'title' => $title, 'body' => $body]);

        $id = (int) $this->pdo->lastInsertId();
        $note = $this->findForUser($id, $userId);

        if ($note === null) {
            throw new \RuntimeException('Failed to load note after insert.');
        }

        return $note;
    }

    public function update(int $id, int $userId, string $title, string $body): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notes SET title = :title, body = :body
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            'title' => $title,
            'body' => $body,
            'id' => $id,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM notes WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }
}
