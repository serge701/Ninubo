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
            c.company_name, c.contact_name, i.number AS invoice_number
        FROM client_briefs b
        LEFT JOIN invoices i ON i.id = b.invoice_id
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

    public static function findByToken(string $token): ?array
    {
        return $token === '' ? null : Database::one(self::SELECT . ' WHERE b.token = ?', [$token]);
    }

    /** Saved answers, or — before the client's first save — the fields we can fill from the CRM client record. */
    public static function answersOrPrefill(array $brief, array $schema): array
    {
        $data = json_decode((string)$brief['data'], true);
        if (is_array($data) && $data) {
            return $data;
        }
        $client = $brief['client_id'] ? Client::find((int)$brief['client_id']) : null;
        if (!$client) {
            return [];
        }
        $values = $client + ['address' => implode(', ', Client::addressLines($client))];
        $prefill = [];
        foreach ($schema as $step) {
            foreach ($step['fields'] as $name => $field) {
                $column = $field['prefill'] ?? null;
                if ($column !== null && trim((string)($values[$column] ?? '')) !== '') {
                    $prefill[$name] = (string)$values[$column];
                }
            }
        }
        return $prefill;
    }

    /**
     * Saves the client's answers from the public form. Draft: status becomes "in progress" on the first save
     * (the form only saves after the client actually changed something). Submit: status "completed".
     * Both write to the client's timeline.
     */
    public static function saveFromClient(array $brief, array $data, int $step, string $lang, bool $submit): void
    {
        $row = [
            // Bad bytes are replaced, and any other encoding failure throws instead of saving an empty value
            // (which would wipe the client's earlier answers).
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR),
            'current_step' => $step,
            'lang' => $lang === 'en' ? 'en' : 'es',
        ];
        if ($submit) {
            $row += ['status' => 'completado', 'completed_at' => date('Y-m-d H:i:s')];
        } elseif ($brief['status'] === 'pendiente') {
            $row['status'] = 'en_progreso';
        }
        Database::update('client_briefs', (int)$brief['id'], $row);

        if ($brief['client_id']) {
            $name = $brief['package_name'] ?: 'website';
            if ($submit) {
                Activity::log((int)$brief['client_id'], null, 'Client submitted the ' . $name . ' brief.');
            } elseif ($brief['status'] === 'pendiente') {
                Activity::log((int)$brief['client_id'], null, 'Client started filling in the ' . $name . ' brief.');
            }
        }
    }

    /** Lets the client edit a submitted brief again (same link). */
    public static function reopen(int $id): void
    {
        Database::run("UPDATE client_briefs SET status = 'en_progreso', completed_at = NULL WHERE id = ? AND status = 'completado'", [$id]);
    }

    public static function forInvoice(int $invoiceId): array
    {
        return Database::all(self::SELECT . ' WHERE b.invoice_id = ? ORDER BY b.id', [$invoiceId]);
    }

    /** New brief link for a client and a package that has a brief profile ($invoiceId: created by issuing it). */
    public static function create(array $client, array $package, int $userId, ?int $invoiceId = null): int
    {
        return Database::insert('client_briefs', [
            'client_id' => (int)$client['id'],
            'package_id' => (int)$package['id'],
            'invoice_id' => $invoiceId,
            'brief_profile' => $package['brief_profile'],
            'token' => bin2hex(random_bytes(20)),
            'client_label' => mb_substr(Client::displayName($client), 0, 190),
            'lang' => $client['preferred_language'] === 'es' ? 'es' : 'en',
            'created_by' => $userId,
        ]);
    }

    /**
     * Runs inside Invoice::issue()'s transaction: one brief per invoiced package that has a brief profile,
     * unless the client already has an open (not submitted) brief for that package.
     * @return string[] timeline messages
     */
    public static function createFromInvoice(array $inv, string $number, array $items, int $userId): array
    {
        $client = Client::find((int)$inv['client_id']);
        $log = [];
        $seen = [];
        foreach ($items as $item) {
            $pkgId = (int)$item['package_id'];
            if ($pkgId === 0 || isset($seen[$pkgId])) {
                continue;
            }
            $seen[$pkgId] = true;
            $pkg = Package::find($pkgId);
            if (!$pkg || $pkg['brief_profile'] === null) {
                continue;
            }
            $open = Database::value(
                "SELECT id FROM client_briefs WHERE client_id = ? AND package_id = ? AND status IN ('pendiente', 'en_progreso') LIMIT 1",
                [(int)$client['id'], $pkgId]
            );
            if ($open !== null) {
                $log[] = 'No new website brief for "' . $pkg['name'] . '" from invoice ' . $number . ': the client already has an open one.';
                continue;
            }
            self::create($client, $pkg, $userId, (int)$inv['id']);
            $log[] = 'Website brief created automatically from invoice ' . $number . ' ("' . $pkg['name'] . '").';
        }
        return $log;
    }

    /**
     * Runs inside Invoice::void(): briefs that invoice created and the client never opened are deleted;
     * started or submitted ones are kept (they are the client's work).
     * @return string[] descriptions of what happened
     */
    public static function undoFromInvoice(int $invoiceId): array
    {
        $changed = [];
        foreach (self::forInvoice($invoiceId) as $b) {
            $name = $b['package_name'] ?: 'Website brief';
            if ($b['status'] === 'pendiente') {
                self::delete((int)$b['id']);
                $changed[] = 'brief "' . $name . '" deleted (never opened)';
            } else {
                $changed[] = 'brief "' . $name . '" kept (' . strtolower(self::STATUSES[$b['status']] ?? $b['status']) . ')';
            }
        }
        return $changed;
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
