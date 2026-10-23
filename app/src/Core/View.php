<?php
declare(strict_types=1);
namespace Core;

class View
{
    public static function render(string $template, array $data = [], bool $returnOnly = false): string
    {
        $path = VIEWS_PATH . '/' . $template . '.php';
        if (!file_exists($path)) {
            throw new \RuntimeException("View không tồn tại: {$template}");
        }

        // Extract data vào scope
        extract($data, EXTR_SKIP);

        ob_start();
        include $path;
        $content = ob_get_clean();

        if ($returnOnly) {
            return $content;
        }

        echo $content;
        return $content;
    }

    public static function renderLayout(string $layout, string $template, array $data = []): void
    {
        $data['content'] = self::render($template, $data, true);
        self::render("layouts/{$layout}", $data);
    }

    /**
     * Escape HTML output chống XSS
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

// Helper toàn cục
function e(mixed $v): string { return \Core\View::e($v); }
function csrf_field(): string { return \Core\Csrf::field(); }
function csrf_token(): string { return \Core\Csrf::token(); }
function flash(string $key, mixed $default = null): mixed { return \Core\Session::getFlash($key, $default); }
function old(string $key, mixed $default = ''): mixed { return \Core\Session::getFlash("old_{$key}", $default); }
function asset(string $path): string { return '/' . ltrim($path, '/'); }
function uploads_url(string $path): string { return UPLOADS_URL . '/' . ltrim($path, '/'); }
