<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;

/**
 * Verifies the CSRF token on state-changing requests. Returns a 419-style
 * response (reusing HTTP 400) when the token is missing or invalid.
 */
final class CsrfMiddleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public static function handle(Request $request): ?Response
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            return null;
        }

        $token = $request->input('_csrf');
        $token = is_string($token) ? $token : null;

        if (!Csrf::verify($token)) {
            return Response::html('Invalid or missing CSRF token.', 400);
        }

        return null;
    }
}
