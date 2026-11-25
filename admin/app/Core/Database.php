<?php
declare(strict_types=1);

namespace Crm\Core;

use PDO;
use PDOStatement;

/** Thin helpers over the site's shared PDO connection (includes/db.php). */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = ninubo_db();
            // Make NOW()/CURRENT_TIMESTAMP match PHP's timezone (America/Chicago).
            self::$pdo->exec("SET time_zone = '" . date('P') . "'");
        }
        return self::$pdo;
    }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function value(string $sql, array $params = []): mixed
    {
        $value = self::run($sql, $params)->fetchColumn();
        return $value === false ? null : $value;
    }

    /** INSERT a row from an associative array; returns the new id. */
    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $table,
            implode(', ', array_map(fn($c) => "`{$c}`", $cols)),
            implode(', ', array_fill(0, count($cols), '?'))
        );
        self::run($sql, array_values($data));
        return (int)self::pdo()->lastInsertId();
    }

    /** UPDATE `table` SET ... WHERE id = ? */
    public static function update(string $table, int $id, array $data): void
    {
        if (!$data) {
            return;
        }
        $sets = implode(', ', array_map(fn($c) => "`{$c}` = ?", array_keys($data)));
        self::run("UPDATE `{$table}` SET {$sets} WHERE id = ?", [...array_values($data), $id]);
    }

    public static function tableExists(string $table): bool
    {
        return (bool)self::value(
            'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            [$table]
        );
    }
}
