<?php

declare(strict_types=1);

use App\Support\Env;

require dirname(__DIR__) . '/vendor/autoload.php';

// Load .env.testing if present, otherwise fall back to whatever the
// environment (e.g. CI) already provides via real environment variables.
$testEnvFile = dirname(__DIR__) . '/.env.testing';
if (is_file($testEnvFile)) {
    Env::load($testEnvFile);
}
