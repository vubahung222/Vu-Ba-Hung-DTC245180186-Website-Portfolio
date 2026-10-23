<?php
/**
 * Database - PDO connection wrapper
 * UTF-8 / utf8mb4 enforced
 */

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $host = $_ENV['MYSQL_HOST'] ?? getenv('MYSQL_HOST') ?: 'mysql';
        $port = $_ENV['MYSQL_PORT'] ?? getenv('MYSQL_PORT') ?: '3306';
        $name = $_ENV['MYSQL_DATABASE'] ?? getenv('MYSQL_DATABASE') ?: 'portfolio_db';
        $user = $_ENV['MYSQL_USER'] ?? getenv('MYSQL_USER') ?: 'portfolio_user';
        $pass = $_ENV['MYSQL_PASSWORD'] ?? getenv('MYSQL_PASSWORD') ?: '';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        $maxRetries = 5;
        $retryDelay = 2; // seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                self::$pdo = new PDO($dsn, $user, $pass, $options);
                // Xác nhận encoding
                self::$pdo->exec("SET CHARACTER SET utf8mb4");
                self::$pdo->exec("SET SESSION collation_connection = 'utf8mb4_unicode_ci'");
                return self::$pdo;
            } catch (PDOException $e) {
                if ($attempt === $maxRetries) {
                    error_log("Database connection failed after {$maxRetries} attempts: " . $e->getMessage());
                    throw new PDOException("Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.");
                }
                error_log("DB connect attempt {$attempt} failed, retrying in {$retryDelay}s...");
                sleep($retryDelay);
            }
        }

        throw new PDOException("Database connection failed.");
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): array|false
    {
        return self::query($sql, $params)->fetch();
    }

    public static function execute(string $sql, array $params = []): int
    {
        return self::query($sql, $params)->rowCount();
    }

    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }

    public static function beginTransaction(): void
    {
        self::getConnection()->beginTransaction();
    }

    public static function commit(): void
    {
        self::getConnection()->commit();
    }

    public static function rollback(): void
    {
        self::getConnection()->rollBack();
    }
}
