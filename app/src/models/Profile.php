<?php
declare(strict_types=1);

use Core\Database as DB;

class Profile
{
    public static function get(): array|false
    {
        return DB::fetchOne("SELECT * FROM `profile` ORDER BY id ASC LIMIT 1");
    }

    public static function update(int $id, array $data): bool
    {
        $fields = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $sql = "UPDATE `profile` SET {$fields} WHERE id = ?";
        $params = array_merge(array_values($data), [$id]);
        return DB::execute($sql, $params) >= 0;
    }

    public static function updateAvatar(int $id, string $path): bool
    {
        return DB::execute("UPDATE `profile` SET avatar = ? WHERE id = ?", [$path, $id]) >= 0;
    }
}
