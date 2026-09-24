<?php
/**
 * @var \App\Notes\Note $note
 * @var array<string, string> $errors
 */
use App\Core\Csrf;
use App\Core\View;

$title = 'Edit note';
require __DIR__ . '/../partials/head.php';
?>
<nav><a href="/notes">Back to notes</a></nav>
<h1>Edit note</h1>
<form method="post" action="/notes/<?= $note->id ?>">
    <?= Csrf::field() ?>
    <input type="hidden" name="_method" value="PUT">
    <label for="title">Title</label>
    <input type="text" id="title" name="title" value="<?= View::e($note->title) ?>" required>
    <?php if (isset($errors['title'])): ?><span class="error"><?= View::e($errors['title']) ?></span><?php endif; ?>

    <label for="body">Body</label>
    <textarea id="body" name="body" required><?= View::e($note->body) ?></textarea>
    <?php if (isset($errors['body'])): ?><span class="error"><?= View::e($errors['body']) ?></span><?php endif; ?>

    <button type="submit">Update</button>
</form>
<?php require __DIR__ . '/../partials/foot.php'; ?>
