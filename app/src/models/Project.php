<?php
declare(strict_types=1);

use Core\Database as DB;

class Project
{
    public static function all(): array
    {
        return DB::fetchAll("SELECT * FROM `projects` ORDER BY is_featured DESC, sort_order ASC, created_at DESC");
    }

    public static function featured(): array
    {
        return DB::fetchAll("SELECT * FROM `projects` WHERE is_featured = 1 ORDER BY sort_order ASC LIMIT 6");
    }

    public static function find(int $id): array|false
    {
        return DB::fetchOne("SELECT * FROM `projects` WHERE id = ?", [$id]);
    }

    public static function create(array $data): int
    {
        DB::execute(
            "INSERT INTO `projects` (title, description, tech_stack, image, github_url, demo_url, is_featured, sort_order) 
             VALUES (?,?,?,?,?,?,?,?)",
            [
                $data['title'],
                $data['description'],
                $data['tech_stack'] ?? null,
                $data['image'] ?? null,
                $data['github_url'] ?? null,
                $data['demo_url'] ?? null,
                (int)($data['is_featured'] ?? 0),
                (int)($data['sort_order'] ?? 100),
            ]
        );
        return (int)DB::lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        return DB::execute(
            "UPDATE `projects` SET title=?, description=?, tech_stack=?, image=?, github_url=?, demo_url=?, is_featured=?, sort_order=? WHERE id=?",
            [
                $data['title'],
                $data['description'],
                $data['tech_stack'] ?? null,
                $data['image'] ?? null,
                $data['github_url'] ?? null,
                $data['demo_url'] ?? null,
                (int)($data['is_featured'] ?? 0),
                (int)($data['sort_order'] ?? 100),
                $id,
            ]
        ) >= 0;
    }

    public static function updateImage(int $id, string $path): bool
    {
        return DB::execute("UPDATE `projects` SET image=? WHERE id=?", [$path, $id]) >= 0;
    }

    public static function delete(int $id): bool
    {
        return DB::execute("DELETE FROM `projects` WHERE id = ?", [$id]) > 0;
    }

    public static function count(): int
    {
        $row = DB::fetchOne("SELECT COUNT(*) as cnt FROM `projects`");
        return (int)($row['cnt'] ?? 0);
    }

    public static function getTechStack(array $project): array
    {
        if (empty($project['tech_stack'])) return [];
        $decoded = json_decode($project['tech_stack'], true);
        if (is_array($decoded)) return $decoded;
        return array_map('trim', explode(',', $project['tech_stack']));
    }
}
