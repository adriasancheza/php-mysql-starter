<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testMatchesSimpleGetRoute(): void
    {
        $router = new Router();
        $router->get('/hello', fn (Request $r): Response => Response::html('hi'));

        $response = $router->dispatch(Request::create('GET', '/hello'));

        self::assertSame(200, $response->status());
        self::assertSame('hi', $response->body());
    }

    public function testMatchesRouteWithParameters(): void
    {
        $router = new Router();
        $router->get('/notes/{id}/edit', function (Request $r): Response {
            return Response::html('edit-' . $r->routeParam('id'));
        });

        $response = $router->dispatch(Request::create('GET', '/notes/42/edit'));

        self::assertSame('edit-42', $response->body());
    }

    public function testReturns404WhenNoRouteMatches(): void
    {
        $router = new Router();
        $router->get('/hello', fn (Request $r): Response => Response::html('hi'));

        $response = $router->dispatch(Request::create('GET', '/nope'));

        self::assertSame(404, $response->status());
    }

    public function testReturns405WhenPathMatchesButMethodDoesNot(): void
    {
        $router = new Router();
        $router->get('/notes', fn (Request $r): Response => Response::html('list'));

        $response = $router->dispatch(Request::create('POST', '/notes'));

        self::assertSame(405, $response->status());
    }

    public function testIgnoresTrailingSlash(): void
    {
        $router = new Router();
        $router->get('/notes', fn (Request $r): Response => Response::html('list'));

        $response = $router->dispatch(Request::create('GET', '/notes/'));

        self::assertSame(200, $response->status());
    }

    public function testSupportsPutMethod(): void
    {
        $router = new Router();
        $router->put('/notes/{id}', function (Request $r): Response {
            return Response::html('updated-' . $r->routeParam('id'));
        });

        $response = $router->dispatch(Request::create('PUT', '/notes/7'));

        self::assertSame('updated-7', $response->body());
    }
}
