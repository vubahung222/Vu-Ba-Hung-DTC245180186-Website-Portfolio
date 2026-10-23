<?php
declare(strict_types=1);
namespace Core;

class Auth
{
    private const SESSION_KEY = 'admin_user';
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public static function check(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    public static function user(): array|null
    {
        return Session::get(self::SESSION_KEY);
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            Session::flash('error', 'Vui lòng đăng nhập để tiếp tục.');
            header('Location: /admin/login');
            exit;
        }
    }

    public static function login(string $email, string $password): bool
    {
        $user = \AdminUser::findByEmail($email);
        if (!$user) {
            return false;
        }

        // Kiểm tra khóa tài khoản
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            Session::flash('error', 'Tài khoản tạm thời bị khóa. Vui lòng thử lại sau.');
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            // Tăng số lần thất bại
            $attempts = $user['login_attempts'] + 1;
            if ($attempts >= self::MAX_ATTEMPTS) {
                $lockedUntil = date('Y-m-d H:i:s', time() + self::LOCKOUT_MINUTES * 60);
                \AdminUser::updateLoginAttempts($user['id'], $attempts, $lockedUntil);
            } else {
                \AdminUser::updateLoginAttempts($user['id'], $attempts, null);
            }
            return false;
        }

        // Đăng nhập thành công
        \AdminUser::updateLoginAttempts($user['id'], 0, null);
        \AdminUser::updateLastLogin($user['id']);

        Session::regenerate();
        Session::set(self::SESSION_KEY, [
            'id'    => $user['id'],
            'email' => $user['email'],
            'name'  => $user['name'],
        ]);

        return true;
    }

    public static function logout(): void
    {
        Session::destroy();
        header('Location: /admin/login');
        exit;
    }
}
