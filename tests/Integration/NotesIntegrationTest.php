<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Auth\AuthService;
use App\Auth\RateLimiter;
use App\Auth\UserRepository;
use App\Notes\NoteRepository;

final class NotesIntegrationTest extends DatabaseTestCase
{
    private NoteRepository $notes;
    private int $userIdA;
    private int $userIdB;

    protected function setUp(): void
    {
        parent::setUp();

        if (!isset($this->pdo)) {
            return; // test was skipped
        }

        $_SESSION = [];
        $users = new UserRepository($this->pdo);
        $auth = new AuthService($users, new RateLimiter($this->pdo));
        $this->notes = new NoteRepository($this->pdo);

        $userA = $auth->register('User A', 'a@example.com', 'password-123');
        $this->userIdA = $userA->id;

        $userB = $auth->register('User B', 'b@example.com', 'password-123');
        $this->userIdB = $userB->id;
    }

    public function testCreateAndRetrieveNote(): void
    {
        $note = $this->notes->create($this->userIdA, 'Groceries', 'Milk, eggs, bread');

        self::assertSame('Groceries', $note->title);

        $found = $this->notes->findForUser($note->id, $this->userIdA);

        self::assertNotNull($found);
        self::assertSame('Groceries', $found->title);
    }

    public function testAllForUserOnlyReturnsOwnNotes(): void
    {
        $this->notes->create($this->userIdA, 'A note 1', 'body');
        $this->notes->create($this->userIdA, 'A note 2', 'body');
        $this->notes->create($this->userIdB, 'B note 1', 'body');

        $notesForA = $this->notes->allForUser($this->userIdA);

        self::assertCount(2, $notesForA);
    }

    public function testUserCannotReadAnotherUsersNote(): void
    {
        $note = $this->notes->create($this->userIdA, 'Private', 'secret content');

        $found = $this->notes->findForUser($note->id, $this->userIdB);

        self::assertNull($found);
    }

    public function testUserCannotUpdateAnotherUsersNote(): void
    {
        $note = $this->notes->create($this->userIdA, 'Original', 'body');

        $updated = $this->notes->update($note->id, $this->userIdB, 'Hacked', 'hacked body');

        self::assertFalse($updated);

        $stillOriginal = $this->notes->findForUser($note->id, $this->userIdA);
        self::assertSame('Original', $stillOriginal?->title);
    }

    public function testUserCannotDeleteAnotherUsersNote(): void
    {
        $note = $this->notes->create($this->userIdA, 'Keep me', 'body');

        $deleted = $this->notes->delete($note->id, $this->userIdB);

        self::assertFalse($deleted);
        self::assertNotNull($this->notes->findForUser($note->id, $this->userIdA));
    }

    public function testOwnerCanUpdateAndDeleteTheirNote(): void
    {
        $note = $this->notes->create($this->userIdA, 'Original', 'body');

        self::assertTrue($this->notes->update($note->id, $this->userIdA, 'Updated', 'new body'));
        self::assertTrue($this->notes->delete($note->id, $this->userIdA));
        self::assertNull($this->notes->findForUser($note->id, $this->userIdA));
    }
}
