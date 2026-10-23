<?php
declare(strict_types=1);

use Core\Database as DB;

class AdminUser
{
    public static function findByEmail(string $email): array|false
    {
        return DB::fetchOne("SELECT * FROM `admin_users` WHERE email = ?", [$email]);
    }

    public static function find(int $id): array|false
    {
        return DB::fetchOne("SELECT * FROM `admin_users` WHERE id = ?", [$id]);
    }

    public static function create(string $email, string $password, string $name = 'Admin'): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        DB::execute(
            "INSERT INTO `admin_users` (email, password_hash, name) VALUES (?,?,?)",
            [$email, $hash, $name]
        );
        return (int)DB::lastInsertId();
    }

    public static function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        return DB::execute(
            "UPDATE `admin_users` SET password_hash = ? WHERE id = ?",
            [$hash, $id]
        ) > 0;
    }

    public static function updateLoginAttempts(int $id, int $attempts, ?string $lockedUntil): void
    {
        DB::execute(
            "UPDATE `admin_users` SET login_attempts = ?, locked_until = ? WHERE id = ?",
            [$attempts, $lockedUntil, $id]
        );
    }

    public static function updateLastLogin(int $id): void
    {
        DB::execute(
            "UPDATE `admin_users` SET last_login = NOW() WHERE id = ?",
            [$id]
        );
    }

    public static function exists(): bool
    {
        $row = DB::fetchOne("SELECT COUNT(*) as cnt FROM `admin_users`");
        return ((int)($row['cnt'] ?? 0)) > 0;
    }
}
