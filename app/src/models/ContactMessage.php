<?php
declare(strict_types=1);

use Core\Database as DB;

class ContactMessage
{
    public static function all(): array
    {
        return DB::fetchAll("SELECT * FROM `contact_messages` ORDER BY is_read ASC, created_at DESC");
    }

    public static function find(int $id): array|false
    {
        return DB::fetchOne("SELECT * FROM `contact_messages` WHERE id = ?", [$id]);
    }

    public static function create(array $data): int
    {
        DB::execute(
            "INSERT INTO `contact_messages` (sender_name, sender_email, subject, message, ip_address)
             VALUES (?,?,?,?,?)",
            [
                $data['sender_name'], $data['sender_email'],
                $data['subject'], $data['message'],
                $data['ip_address'] ?? null,
            ]
        );
        return (int)DB::lastInsertId();
    }

    public static function markRead(int $id): bool
    {
        return DB::execute(
            "UPDATE `contact_messages` SET is_read = 1, read_at = NOW() WHERE id = ?",
            [$id]
        ) > 0;
    }

    public static function delete(int $id): bool
    {
        return DB::execute("DELETE FROM `contact_messages` WHERE id = ?", [$id]) > 0;
    }

    public static function countUnread(): int
    {
        $row = DB::fetchOne("SELECT COUNT(*) as cnt FROM `contact_messages` WHERE is_read = 0");
        return (int)($row['cnt'] ?? 0);
    }

    public static function count(): int
    {
        $row = DB::fetchOne("SELECT COUNT(*) as cnt FROM `contact_messages`");
        return (int)($row['cnt'] ?? 0);
    }
}
