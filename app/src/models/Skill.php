<?php
declare(strict_types=1);

use Core\Database as DB;

class Skill
{
    public static function all(): array
    {
        return DB::fetchAll("SELECT * FROM `skills` ORDER BY sort_order ASC, name ASC");
    }

    public static function byCategory(): array
    {
        $skills = self::all();
        $grouped = [];
        foreach ($skills as $skill) {
            $grouped[$skill['category']][] = $skill;
        }
        return $grouped;
    }

    public static function find(int $id): array|false
    {
        return DB::fetchOne("SELECT * FROM `skills` WHERE id = ?", [$id]);
    }

    public static function create(array $data): int
    {
        DB::execute(
            "INSERT INTO `skills` (name, category, level, icon, sort_order) VALUES (?,?,?,?,?)",
            [$data['name'], $data['category'], (int)$data['level'], $data['icon'] ?? null, (int)($data['sort_order'] ?? 100)]
        );
        return (int)DB::lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        return DB::execute(
            "UPDATE `skills` SET name=?, category=?, level=?, icon=?, sort_order=? WHERE id=?",
            [$data['name'], $data['category'], (int)$data['level'], $data['icon'] ?? null, (int)($data['sort_order'] ?? 100), $id]
        ) >= 0;
    }

    public static function delete(int $id): bool
    {
        return DB::execute("DELETE FROM `skills` WHERE id = ?", [$id]) > 0;
    }

    public static function count(): int
    {
        $row = DB::fetchOne("SELECT COUNT(*) as cnt FROM `skills`");
        return (int)($row['cnt'] ?? 0);
    }
}
