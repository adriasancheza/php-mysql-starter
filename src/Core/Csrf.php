<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CSRF token generation and verification, backed by the session.
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        $token = Session::get(self::SESSION_KEY);

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::put(self::SESSION_KEY, $token);
        }

        return $token;
    }

    public static function verify(?string $submittedToken): bool
    {
        $sessionToken = Session::get(self::SESSION_KEY);

        if (!is_string($sessionToken) || !is_string($submittedToken) || $submittedToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $submittedToken);
    }

    /**
     * HTML for a hidden input carrying the current CSRF token.
     */
    public static function field(): string
    {
        $token = View::e(self::token());

        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }
}
