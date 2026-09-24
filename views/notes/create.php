<?php
/**
 * @var array<string, string> $errors
 * @var array<string, string> $old
 */
use App\Core\Csrf;
use App\Core\View;

$title = 'New note';
require __DIR__ . '/../partials/head.php';
?>
<nav><a href="/notes">Back to notes</a></nav>
<h1>New note</h1>
<form method="post" action="/notes">
    <?= Csrf::field() ?>
    <label for="title">Title</label>
    <input type="text" id="title" name="title" value="<?= View::e($old['title'] ?? '') ?>" required>
    <?php if (isset($errors['title'])): ?><span class="error"><?= View::e($errors['title']) ?></span><?php endif; ?>

    <label for="body">Body</label>
    <textarea id="body" name="body" required><?= View::e($old['body'] ?? '') ?></textarea>
    <?php if (isset($errors['body'])): ?><span class="error"><?= View::e($errors['body']) ?></span><?php endif; ?>

    <button type="submit">Save</button>
</form>
<?php require __DIR__ . '/../partials/foot.php'; ?>
