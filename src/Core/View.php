<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Very small PHP-template view renderer. Templates are plain .php files
 * under views/ that receive extracted variables and may call e()/csrfField().
 */
final class View
{
    public function __construct(private readonly string $viewsPath)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $file = rtrim($this->viewsPath, '/\\') . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $template) . '.php';

        if (!is_file($file)) {
            throw new RuntimeException("View template not found: {$template}");
        }

        $renderer = static function (string $__file, array $__data): string {
            extract($__data, EXTR_SKIP);
            ob_start();
            require $__file;

            return (string) ob_get_clean();
        };

        return $renderer($file, $data);
    }

    /**
     * Escape a value for safe HTML output.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
