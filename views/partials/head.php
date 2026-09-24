<?php
/** @var string $title */
use App\Core\View;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= View::e($title ?? 'php-mysql-starter') ?></title>
    <style>
        :root { color-scheme: light dark; }
        body { font-family: system-ui, -apple-system, "Segoe UI", sans-serif; max-width: 640px; margin: 3rem auto; padding: 0 1rem; line-height: 1.5; }
        nav { display: flex; gap: 1rem; margin-bottom: 2rem; }
        nav a { text-decoration: none; }
        form { display: flex; flex-direction: column; gap: 0.75rem; max-width: 420px; }
        label { font-weight: 600; }
        input[type=text], input[type=email], input[type=password], textarea { padding: 0.5rem; font-size: 1rem; }
        textarea { min-height: 120px; }
        button { padding: 0.5rem 1rem; font-size: 1rem; cursor: pointer; width: fit-content; }
        .error { color: #b91c1c; font-size: 0.9rem; }
        .flash-success { color: #15803d; }
        .note { border: 1px solid #d1d5db; border-radius: 6px; padding: 1rem; margin-bottom: 1rem; }
        .note-actions { display: flex; gap: 0.75rem; margin-top: 0.5rem; }
    </style>
</head>
<body>
<?php
$flashes = \App\Core\Session::pullFlashes();
foreach ($flashes as $type => $message):
?>
    <p class="flash-<?= View::e($type) ?>"><?= View::e($message) ?></p>
<?php endforeach; ?>
