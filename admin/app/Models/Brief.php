<?php
declare(strict_types=1);

namespace Crm\Models;

use Crm\Core\Database;

/** Website-information briefs (table client_briefs, filled by the client at /brief.php?token=...). */
final class Brief
{
    public const STATUSES = ['pendiente' => 'Not started', 'en_progreso' => 'In progress', 'completado' => 'Completed'];
    public const STATUS_COLORS = ['pendiente' => 'secondary', 'en_progreso' => 'yellow', 'completado' => 'green'];

    public static function all(?string $status = null): array
    {
        $sql = 'SELECT b.*, u.name AS created_by_name FROM client_briefs b LEFT JOIN users u ON u.id = b.created_by';
        $params = [];
        if ($status !== null && isset(self::STATUSES[$status])) {
            $sql .= ' WHERE b.status = ?';
            $params[] = $status;
        }
        return Database::all($sql . ' ORDER BY b.updated_at DESC', $params);
    }

    public static function find(int $id): ?array
    {
        return Database::one(
            'SELECT b.*, u.name AS created_by_name FROM client_briefs b LEFT JOIN users u ON u.id = b.created_by WHERE b.id = ?',
            [$id]
        );
    }

    public static function create(string $label, int $userId): int
    {
        return Database::insert('client_briefs', [
            'token' => bin2hex(random_bytes(20)),
            'client_label' => $label,
            'created_by' => $userId,
        ]);
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM client_briefs WHERE id = ?', [$id]);
    }

    /** @return array<string,int> status => count */
    public static function counts(): array
    {
        $rows = Database::all('SELECT status, COUNT(*) AS n FROM client_briefs GROUP BY status');
        return array_merge(array_fill_keys(array_keys(self::STATUSES), 0), array_column($rows, 'n', 'status'));
    }

    /** Public link the client opens (always built without ?lang so the token query string stays valid). */
    public static function publicLink(array $brief): string
    {
        return ninubo_absolute_url('brief.php', 'es') . '?token=' . urlencode($brief['token']);
    }
}
