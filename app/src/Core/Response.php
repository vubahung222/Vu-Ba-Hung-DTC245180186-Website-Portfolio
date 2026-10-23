<?php
declare(strict_types=1);
namespace Core;

class Response
{
    public static function redirect(string $url, int $code = 302): never
    {
        http_response_code($code);
        header("Location: {$url}");
        exit;
    }

    public static function json(mixed $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    public static function back(string $fallback = '/'): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;
        self::redirect($referer);
    }
}
