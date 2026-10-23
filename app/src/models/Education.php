<?php
declare(strict_types=1);

use Core\Database as DB;

class Education
{
    public static function all(): array
    {
        return DB::fetchAll("SELECT * FROM `education` ORDER BY start_year DESC, sort_order ASC");
    }

    public static function find(int $id): array|false
    {
        return DB::fetchOne("SELECT * FROM `education` WHERE id = ?", [$id]);
    }

    public static function create(array $data): int
    {
        DB::execute(
            "INSERT INTO `education` (institution, degree, field_of_study, start_year, end_year, description, gpa, sort_order)
             VALUES (?,?,?,?,?,?,?,?)",
            [
                $data['institution'], $data['degree'], $data['field_of_study'] ?? null,
                (int)$data['start_year'], !empty($data['end_year']) ? (int)$data['end_year'] : null,
                $data['description'] ?? null, $data['gpa'] ?? null, (int)($data['sort_order'] ?? 100),
            ]
        );
        return (int)DB::lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        return DB::execute(
            "UPDATE `education` SET institution=?, degree=?, field_of_study=?, start_year=?, end_year=?, description=?, gpa=?, sort_order=? WHERE id=?",
            [
                $data['institution'], $data['degree'], $data['field_of_study'] ?? null,
                (int)$data['start_year'], !empty($data['end_year']) ? (int)$data['end_year'] : null,
                $data['description'] ?? null, $data['gpa'] ?? null, (int)($data['sort_order'] ?? 100), $id,
            ]
        ) >= 0;
    }

    public static function delete(int $id): bool
    {
        return DB::execute("DELETE FROM `education` WHERE id = ?", [$id]) > 0;
    }

    public static function count(): int
    {
        $row = DB::fetchOne("SELECT COUNT(*) as cnt FROM `education`");
        return (int)($row['cnt'] ?? 0);
    }
}
