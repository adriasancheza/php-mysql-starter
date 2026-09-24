<?php
/**
 * @var \App\Auth\User $user
 * @var list<\App\Notes\Note> $notes
 */
use App\Core\Csrf;
use App\Core\View;

$title = 'My notes';
require __DIR__ . '/../partials/head.php';
?>
<nav>
    <span>Hi, <?= View::e($user->name) ?></span>
    <a href="/notes">Notes</a>
    <a href="/notes/create">New note</a>
    <form method="post" action="/logout" style="display:inline">
        <?= Csrf::field() ?>
        <button type="submit">Log out</button>
    </form>
</nav>
<h1>My notes</h1>
<?php if ($notes === []): ?>
    <p>You have no notes yet. <a href="/notes/create">Create one</a>.</p>
<?php endif; ?>
<?php foreach ($notes as $note): ?>
    <div class="note">
        <h2><?= View::e($note->title) ?></h2>
        <p><?= nl2br(View::e($note->body)) ?></p>
        <div class="note-actions">
            <a href="/notes/<?= $note->id ?>/edit">Edit</a>
            <form method="post" action="/notes/<?= $note->id ?>/delete" onsubmit="return confirm('Delete this note?');">
                <?= Csrf::field() ?>
                <button type="submit">Delete</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../partials/foot.php'; ?>
