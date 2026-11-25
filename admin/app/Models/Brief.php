<?php
declare(strict_types=1);

namespace Crm\Models;

use Crm\Core\Database;

/**
 * Website-information briefs (table client_briefs). Created from a client + package in the CRM;
 * the client fills it at /brief.php?token=... with the questions of the package's brief profile.
 */
final class Brief
{
    public const STATUSES = ['pendiente' => 'Not started', 'en_progreso' => 'In progress', 'completado' => 'Completed'];
    public const STATUS_COLORS = ['pendiente' => 'secondary', 'en_progreso' => 'yellow', 'completado' => 'green'];

    private const SELECT = 'SELECT b.*, u.name AS created_by_name, p.name AS package_name,
            c.company_name, c.contact_name
        FROM client_briefs b
        LEFT JOIN users u ON u.id = b.created_by
        LEFT JOIN packages p ON p.id = b.package_id
        LEFT JOIN clients c ON c.id = b.client_id';

    public static function all(?string $status = null): array
    {
        $sql = self::SELECT;
        $params = [];
        if ($status !== null && isset(self::STATUSES[$status])) {
            $sql .= ' WHERE b.status = ?';
            $params[] = $status;
        }
        return Database::all($sql . ' ORDER BY b.updated_at DESC', $params);
    }

    public static function forClient(int $clientId): array
    {
        return Database::all(self::SELECT . ' WHERE b.client_id = ? ORDER BY b.created_at DESC', [$clientId]);
    }

    public static function find(int $id): ?array
    {
        return Database::one(self::SELECT . ' WHERE b.id = ?', [$id]);
    }

    /** New brief link for a client and a package that has a brief profile. */
    public static function create(array $client, array $package, int $userId): int
    {
        return Database::insert('client_briefs', [
            'client_id' => (int)$client['id'],
            'package_id' => (int)$package['id'],
            'brief_profile' => $package['brief_profile'],
            'token' => bin2hex(random_bytes(20)),
            'client_label' => mb_substr(Client::displayName($client), 0, 190),
            'lang' => $client['preferred_language'] === 'es' ? 'es' : 'en',
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
        return array_map('intval', array_merge(array_fill_keys(array_keys(self::STATUSES), 0), array_column($rows, 'n', 'status')));
    }

    /** Public link the client opens (built without ?lang so the token query string stays valid). */
    public static function publicLink(array $brief): string
    {
        return ninubo_absolute_url('brief.php', 'es') . '?token=' . urlencode($brief['token']);
    }

    public static function profileLabel(?string $profile): string
    {
        return $profile !== null ? (ninubo_brief_profiles()[$profile] ?? $profile) : 'Full questionnaire';
    }
}
