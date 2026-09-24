<?php
/**
 * @var array<string, string> $errors
 * @var array<string, string> $old
 */
use App\Core\Csrf;
use App\Core\View;

$title = 'Register';
require __DIR__ . '/../partials/head.php';
?>
<nav><a href="/login">Log in</a><a href="/register">Register</a></nav>
<h1>Create an account</h1>
<form method="post" action="/register">
    <?= Csrf::field() ?>
    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= View::e($old['name'] ?? '') ?>" required>
    <?php if (isset($errors['name'])): ?><span class="error"><?= View::e($errors['name']) ?></span><?php endif; ?>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= View::e($old['email'] ?? '') ?>" required>
    <?php if (isset($errors['email'])): ?><span class="error"><?= View::e($errors['email']) ?></span><?php endif; ?>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required minlength="8">
    <?php if (isset($errors['password'])): ?><span class="error"><?= View::e($errors['password']) ?></span><?php endif; ?>

    <label for="password_confirmation">Confirm password</label>
    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">

    <button type="submit">Register</button>
</form>
<?php require __DIR__ . '/../partials/foot.php'; ?>
