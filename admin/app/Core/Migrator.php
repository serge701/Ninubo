<?php
declare(strict_types=1);

namespace Crm\Core;

/**
 * Applies admin/database/migrations/*.sql in filename order and records each one in
 * `schema_migrations`. Admins apply pending migrations from the banner in the UI
 * (works on shared hosting without a shell) or with `php admin/scripts/migrate.php`.
 *
 * Files must contain plain SQL statements, each ending with ";" at the end of a line.
 */
final class Migrator
{
    private static function dir(): string
    {
        return CRM_ROOT . '/database/migrations';
    }

    private static function ensureTable(): void
    {
        Database::pdo()->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                migration VARCHAR(190) NOT NULL PRIMARY KEY,
                applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /** @return string[] migration filenames, sorted */
    public static function files(): array
    {
        $files = array_map('basename', glob(self::dir() . '/*.sql') ?: []);
        sort($files, SORT_STRING);
        return $files;
    }

    /** @return string[] */
    public static function pending(): array
    {
        if (!Database::tableExists('schema_migrations')) {
            return self::files();
        }
        $applied = array_column(Database::all('SELECT migration FROM schema_migrations'), 'migration');
        return array_values(array_diff(self::files(), $applied));
    }

    /** Applies every pending migration; returns the names applied. Throws on the first failure. */
    public static function migrate(): array
    {
        self::ensureTable();
        $done = [];
        foreach (self::pending() as $file) {
            $sql = (string)file_get_contents(self::dir() . '/' . $file);
            foreach (self::statements($sql) as $statement) {
                try {
                    Database::pdo()->exec($statement);
                } catch (\Throwable $e) {
                    throw new \RuntimeException("Migration {$file} failed: " . $e->getMessage(), 0, $e);
                }
            }
            Database::run('INSERT INTO schema_migrations (migration) VALUES (?)', [$file]);
            $done[] = $file;
        }
        return $done;
    }

    /** Splits a migration file into statements (";" at end of line), dropping "--" comment lines. */
    private static function statements(string $sql): array
    {
        $lines = array_filter(
            preg_split('/\R/', $sql) ?: [],
            fn($line) => !str_starts_with(ltrim($line), '--')
        );
        $parts = preg_split('/;\s*(?:\R|$)/', implode("\n", $lines)) ?: [];
        return array_values(array_filter(array_map('trim', $parts), fn($s) => $s !== ''));
    }
}
