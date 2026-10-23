<?php
/**
 * Global helper functions
 */

declare(strict_types=1);

if (!function_exists('e')) {
    function e(mixed $v): string
    {
        return \Core\View::e($v);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return \Core\Csrf::field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \Core\Csrf::token();
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $default = null): mixed
    {
        return \Core\Session::getFlash($key, $default);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return \Core\Session::getFlash("old_{$key}", $default);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('uploads_url')) {
    function uploads_url(string $path): string
    {
        return UPLOADS_URL . '/' . ltrim($path, '/');
    }
}
