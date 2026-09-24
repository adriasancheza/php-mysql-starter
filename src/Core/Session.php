<?php

declare(strict_types=1);

namespace App\Core;

use App\Support\Env;

/**
 * Session bootstrap with hardened cookie params, plus a tiny flash-message
 * helper (read-once values stored in the session).
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => Env::getBool('SESSION_SECURE_COOKIE', false),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();

        // Rotate the session id once per "session lifetime" to limit fixation
        // risk, without rotating on every single request.
        if (!isset($_SESSION['_started_at'])) {
            $_SESSION['_started_at'] = time();
            self::regenerate();
        }
    }

    public static function regenerate(): void
    {
        // No-op outside of an active PHP session (e.g. in tests that only
        // stub $_SESSION directly) — session_regenerate_id() would otherwise
        // raise a warning.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'],
            ]);
        }

        session_destroy();
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    /**
     * Read and clear all flash messages.
     *
     * @return array<string, string>
     */
    public static function pullFlashes(): array
    {
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        /** @var array<string, string> $flashes */
        return $flashes;
    }
}
