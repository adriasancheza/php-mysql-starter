<?php

declare(strict_types=1);

use App\Support\Env;

require_once __DIR__ . '/../vendor/autoload.php';

Env::load(dirname(__DIR__) . '/.env');

date_default_timezone_set('UTC');

if (Env::getBool('APP_DEBUG', false)) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}
