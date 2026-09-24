<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Auth\AuthService;
use App\Core\Request;
use App\Core\Response;

/**
 * Guards a route so it can only be reached by a logged-in user; otherwise
 * redirects to /login.
 */
final class AuthMiddleware
{
    public static function handle(Request $request, AuthService $auth): ?Response
    {
        if (!$auth->check()) {
            return Response::redirect('/login');
        }

        return null;
    }
}
