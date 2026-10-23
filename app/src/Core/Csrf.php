<?php
declare(strict_types=1);
namespace Core;

class Csrf
{
    private const TOKEN_KEY = '_csrf_token';
    private const TOKEN_LENGTH = 32;

    public static function generate(): string
    {
        if (!Session::has(self::TOKEN_KEY)) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(self::TOKEN_LENGTH)));
        }
        return Session::get(self::TOKEN_KEY);
    }

    public static function token(): string
    {
        return self::generate();
    }

    public static function field(): string
    {
        $token = self::generate();
        return sprintf('<input type="hidden" name="_csrf_token" value="%s">', htmlspecialchars($token, ENT_QUOTES, 'UTF-8'));
    }

    public static function verify(?string $token): bool
    {
        $stored = Session::get(self::TOKEN_KEY);
        if ($stored === null || $token === null) {
            return false;
        }
        return hash_equals($stored, $token);
    }

    public static function validateOrFail(): void
    {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!self::verify($token)) {
            http_response_code(403);
            die(json_encode(['error' => 'CSRF token không hợp lệ.']));
        }
        // Regenerate after use
        Session::set(self::TOKEN_KEY, bin2hex(random_bytes(self::TOKEN_LENGTH)));
    }
}
