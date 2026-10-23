<?php
declare(strict_types=1);

use Core\Database as DB;

class Experience
{
    public static function all(): array
    {
        return DB::fetchAll("SELECT * FROM `experiences` ORDER BY start_date DESC, sort_order ASC");
    }

    public static function find(int $id): array|false
    {
        return DB::fetchOne("SELECT * FROM `experiences` WHERE id = ?", [$id]);
    }

    public static function create(array $data): int
    {
        DB::execute(
            "INSERT INTO `experiences` (company, position, start_date, end_date, description, location, sort_order)
             VALUES (?,?,?,?,?,?,?)",
            [
                $data['company'], $data['position'],
                $data['start_date'], $data['end_date'] ?: null,
                $data['description'] ?? null, $data['location'] ?? null,
                (int)($data['sort_order'] ?? 100),
            ]
        );
        return (int)DB::lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        return DB::execute(
            "UPDATE `experiences` SET company=?, position=?, start_date=?, end_date=?, description=?, location=?, sort_order=? WHERE id=?",
            [
                $data['company'], $data['position'],
                $data['start_date'], $data['end_date'] ?: null,
                $data['description'] ?? null, $data['location'] ?? null,
                (int)($data['sort_order'] ?? 100), $id,
            ]
        ) >= 0;
    }

    public static function delete(int $id): bool
    {
        return DB::execute("DELETE FROM `experiences` WHERE id = ?", [$id]) > 0;
    }

    public static function count(): int
    {
        $row = DB::fetchOne("SELECT COUNT(*) as cnt FROM `experiences`");
        return (int)($row['cnt'] ?? 0);
    }

    public static function formatPeriod(array $exp): string
    {
        $months = ['01'=>'Tháng 1','02'=>'Tháng 2','03'=>'Tháng 3','04'=>'Tháng 4',
                   '05'=>'Tháng 5','06'=>'Tháng 6','07'=>'Tháng 7','08'=>'Tháng 8',
                   '09'=>'Tháng 9','10'=>'Tháng 10','11'=>'Tháng 11','12'=>'Tháng 12'];
        $start = date('Y', strtotime($exp['start_date']));
        if ($exp['end_date']) {
            $end = date('Y', strtotime($exp['end_date']));
            return $start === $end ? $start : "{$start} – {$end}";
        }
        return "{$start} – Hiện tại";
    }
}
