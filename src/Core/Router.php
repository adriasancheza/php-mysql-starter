<?php

declare(strict_types=1);

namespace App\Core;

/**
 * A tiny GET/POST(/PUT/PATCH/DELETE) router with named parameters like
 * /notes/{id}. No regex DSL, no middleware pipeline — just enough for a
 * small starter app.
 */
final class Router
{
    /**
     * @var list<array{method: string, pattern: string, handler: callable, paramNames: list<string>}>
     */
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->addRoute('POST', $pattern, $handler);
    }

    public function put(string $pattern, callable $handler): void
    {
        $this->addRoute('PUT', $pattern, $handler);
    }

    public function patch(string $pattern, callable $handler): void
    {
        $this->addRoute('PATCH', $pattern, $handler);
    }

    public function delete(string $pattern, callable $handler): void
    {
        $this->addRoute('DELETE', $pattern, $handler);
    }

    private function addRoute(string $method, string $pattern, callable $handler): void
    {
        $paramNames = [];
        $regex = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            function (array $matches) use (&$paramNames): string {
                $paramNames[] = $matches[1];

                return '(?P<' . $matches[1] . '>[^/]+)';
            },
            rtrim($pattern, '/') === '' ? '/' : rtrim($pattern, '/'),
        );

        $this->routes[] = [
            'method' => $method,
            'pattern' => '#^' . $regex . '$#',
            'handler' => $handler,
            'paramNames' => $paramNames,
        ];
    }

    /**
     * Dispatch the request. Returns a 404 Response if nothing matches, and
     * a 405 Response if the path matches but not the HTTP method.
     */
    public function dispatch(Request $request): Response
    {
        $path = rtrim($request->path(), '/');
        $path = $path === '' ? '/' : $path;

        $pathMatchedForOtherMethod = false;

        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $path, $matches) !== 1) {
                continue;
            }

            if ($route['method'] !== $request->method()) {
                $pathMatchedForOtherMethod = true;

                continue;
            }

            $params = [];
            foreach ($route['paramNames'] as $name) {
                $params[$name] = (string) $matches[$name];
            }

            $request->setRouteParams($params);

            /** @var Response $result */
            $result = ($route['handler'])($request);

            return $result;
        }

        if ($pathMatchedForOtherMethod) {
            return Response::html('Method Not Allowed', 405);
        }

        return Response::notFound();
    }
}
