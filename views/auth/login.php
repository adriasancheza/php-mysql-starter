<?php
/**
 * @var string|null $error
 * @var array<string, string> $old
 */
use App\Core\Csrf;
use App\Core\View;

$title = 'Log in';
require __DIR__ . '/../partials/head.php';
?>
<nav><a href="/login">Log in</a><a href="/register">Register</a></nav>
<h1>Log in</h1>
<?php if ($error !== null): ?><p class="error"><?= View::e($error) ?></p><?php endif; ?>
<form method="post" action="/login">
    <?= Csrf::field() ?>
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= View::e($old['email'] ?? '') ?>" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Log in</button>
</form>
<?php require __DIR__ . '/../partials/foot.php'; ?>
