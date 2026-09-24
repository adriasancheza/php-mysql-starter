<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Represents an outgoing HTTP response. Building a Response does not send
 * anything by itself; call send() once, typically from the front controller.
 */
final class Response
{
    /** @var array<string, string> */
    private array $headers = [];

    private function __construct(
        private string $body,
        private int $status = 200,
    ) {
    }

    public static function html(string $body, int $status = 200): self
    {
        $response = new self($body, $status);
        $response->headers['Content-Type'] = 'text/html; charset=UTF-8';

        return $response;
    }

    /**
     * @param array<mixed, mixed> $data
     */
    public static function json(array $data, int $status = 200): self
    {
        $body = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $response = new self($body, $status);
        $response->headers['Content-Type'] = 'application/json; charset=UTF-8';

        return $response;
    }

    public static function redirect(string $location, int $status = 302): self
    {
        $response = new self('', $status);
        $response->headers['Location'] = $location;

        return $response;
    }

    public static function notFound(string $body = 'Not Found'): self
    {
        return self::html($body, 404);
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    public function withStatus(int $status): self
    {
        $clone = clone $this;
        $clone->status = $status;

        return $clone;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function body(): string
    {
        return $this->body;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);

            foreach ($this->headers as $name => $value) {
                header($name . ': ' . $value);
            }
        }

        echo $this->body;
    }
}
