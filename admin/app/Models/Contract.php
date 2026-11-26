<?php
declare(strict_types=1);

namespace Crm\Models;

use Crm\Core\Database;
use Crm\Core\Settings;
use Crm\Core\View;

/**
 * Service agreements. Lifecycle:
 *   draft (editable) → sent (finalized: number, frozen text + SHA-256 hash, signing link)
 *   → client_signed (client e-signed via the link) → signed (Ninubo countersigned, or a pen-signed copy was uploaded).
 * Every step is written to contract_events — that log is the evidence for the electronic signature
 * (Texas UETA, Tex. Bus. & Com. Code ch. 322, and the federal ESIGN Act).
 */
final class Contract
{
    public const STATUSES = [
        'draft' => 'Draft', 'sent' => 'Awaiting client signature', 'client_signed' => 'Signed by client',
        'signed' => 'Fully signed', 'cancelled' => 'Cancelled',
    ];
    public const STATUS_COLORS = ['draft' => 'secondary', 'sent' => 'blue', 'client_signed' => 'yellow', 'signed' => 'green', 'cancelled' => 'dark'];
    public const EVENTS = [
        'created' => 'Draft created', 'updated' => 'Draft updated', 'finalized' => 'Finalized and signing link created',
        'viewed' => 'Opened by the client', 'consented' => 'Client agreed to use electronic records and signatures',
        'client_signed' => 'Signed electronically by the client', 'countersigned' => 'Countersigned by the provider',
        'pdf_downloaded' => 'PDF downloaded', 'signed_copy_uploaded' => 'Pen-signed copy uploaded', 'cancelled' => 'Cancelled',
    ];

    private const SELECT = 'SELECT k.*, c.company_name, c.contact_name, i.number AS invoice_number, u.name AS created_by_name
        FROM contracts k
        JOIN clients c ON c.id = k.client_id
        LEFT JOIN invoices i ON i.id = k.invoice_id
        LEFT JOIN users u ON u.id = k.created_by';

    public static function find(int $id): ?array
    {
        return Database::one(self::SELECT . ' WHERE k.id = ?', [$id]);
    }

    public static function findByToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{48}$/', $token)) {
            return null;
        }
        return Database::one(self::SELECT . ' WHERE k.token = ?', [$token]);
    }

    public static function forClient(int $clientId): array
    {
        return Database::all(self::SELECT . ' WHERE k.client_id = ? ORDER BY k.created_at DESC', [$clientId]);
    }

    public static function search(string $status = ''): array
    {
        $where = isset(self::STATUSES[$status]) ? ' WHERE k.status = ?' : '';
        return Database::all(self::SELECT . $where . ' ORDER BY k.updated_at DESC LIMIT 200', $where ? [$status] : []);
    }

    public static function counts(): array
    {
        $rows = Database::all('SELECT status, COUNT(*) AS n FROM contracts GROUP BY status');
        return array_map('intval', array_merge(array_fill_keys(array_keys(self::STATUSES), 0), array_column($rows, 'n', 'status')));
    }

    public static function displayNumber(array $k): string
    {
        return $k['number'] ?: 'Draft #' . $k['id'];
    }

    public static function events(int $contractId): array
    {
        return Database::all(
            'SELECT e.*, u.name AS user_name FROM contract_events e LEFT JOIN users u ON u.id = e.user_id
             WHERE e.contract_id = ? ORDER BY e.created_at, e.id',
            [$contractId]
        );
    }

    public static function logEvent(int $contractId, string $event, string $details = '', ?int $userId = null): void
    {
        Database::insert('contract_events', [
            'contract_id' => $contractId,
            'event' => $event,
            'details' => mb_substr($details, 0, 500),
            'ip' => substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
            'user_agent' => mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
            'user_id' => $userId,
        ]);
    }

    /** Invoices this contract can be based on: the client's issued, non-void invoices. */
    public static function invoiceOptions(int $clientId): array
    {
        return Database::all(
            "SELECT id, number, total, issue_date FROM invoices WHERE client_id = ? AND status IN ('sent','partial','paid')
             ORDER BY issue_date DESC, id DESC",
            [$clientId]
        );
    }

    public static function create(array $data, int $userId): int
    {
        return Database::insert('contracts', $data + ['status' => 'draft', 'created_by' => $userId]);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('contracts', $id, $data);
    }

    // ---------------------------------------------------------------- agreement text

    /** Everything the agreement template needs, computed from the client, the invoice and Settings. */
    public static function templateData(array $contract): array
    {
        $client = Client::find((int)$contract['client_id']);
        $invoice = $contract['invoice_id'] ? Invoice::find((int)$contract['invoice_id']) : null;
        $items = $invoice ? Invoice::items((int)$invoice['id']) : [];
        $renewal = Package::find((int)Settings::get('website_renewal_package_id'));

        $oneTime = array_values(array_filter($items, fn($i) => $i['billing'] === 'one_time'));
        $plans = array_values(array_filter($items, fn($i) => $i['billing'] === 'monthly'));
        $hasWebsite = false;
        foreach ($oneTime as $i) {
            $p = $i['package_id'] ? Package::find((int)$i['package_id']) : null;
            if ($p && $p['category'] === 'website' && $p['brief_profile'] !== null) {
                $hasWebsite = true;
            }
        }
        // Care plans are described with the catalog text (the invoice line says "first month").
        $planInfo = array_map(function ($i) {
            $p = $i['package_id'] ? Package::find((int)$i['package_id']) : null;
            return [
                'name' => $p['name'] ?? preg_replace('/\s*\(first month\)$/i', '', $i['description']),
                'description' => $p['description'] ?? $i['details'],
                'price' => (float)$i['unit_price'],
            ];
        }, $plans);

        return [
            'contract' => $contract,
            'client' => $client,
            'invoice' => $invoice,
            'oneTime' => $oneTime,
            'plans' => $planInfo,
            'schedule' => $invoice ? Invoice::schedule($invoice) : [],
            'hasWebsite' => $hasWebsite,
            'renewalPrice' => $renewal ? (float)$renewal['price'] : null,
            's' => Settings::all(),
        ];
    }

    public static function renderBody(array $contract): string
    {
        return View::partial('contracts/agreement', self::templateData($contract));
    }

    /**
     * Freezes the text (hash), assigns the agreement number and creates the signing link.
     */
    public static function finalize(array $contract, int $userId): array
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $next = Database::value("SELECT setting_value FROM settings WHERE setting_key = 'contract_next_number' FOR UPDATE");
            $next = ctype_digit((string)$next) ? (int)$next : (int)Settings::get('contract_next_number');
            do {
                $number = Settings::get('contract_prefix') . $next;
                $taken = (bool)Database::value('SELECT COUNT(*) FROM contracts WHERE number = ?', [$number]);
                $next++;
            } while ($taken);
            Database::run(
                "INSERT INTO settings (setting_key, setting_value) VALUES ('contract_next_number', ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                [(string)$next]
            );

            $contract['number'] = $number; // the text shows its own number
            $body = self::renderBody($contract);
            Database::update('contracts', (int)$contract['id'], [
                'number' => $number,
                'status' => 'sent',
                'body_html' => $body,
                'body_hash' => hash('sha256', $body),
                'token' => bin2hex(random_bytes(24)),
                'sent_at' => date('Y-m-d H:i:s'),
            ]);
            self::logEvent((int)$contract['id'], 'finalized', "Agreement {$number}; document SHA-256 " . hash('sha256', $body), $userId);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return self::find((int)$contract['id']);
    }

    /** Public link the client opens to read and sign. */
    public static function signLink(array $contract): string
    {
        return ninubo_absolute_url('sign.php', 'es') . '?token=' . $contract['token'];
    }

    /**
     * Validates a drawn signature posted as a PNG data URI. Returns the clean data URI or null.
     */
    public static function cleanSignature(string $dataUri): ?string
    {
        if (!preg_match('#^data:image/png;base64,([A-Za-z0-9+/=]+)$#', $dataUri, $m)) {
            return null;
        }
        $bin = base64_decode($m[1], true);
        if ($bin === false || strlen($bin) < 200 || strlen($bin) > 400000 || !str_starts_with($bin, "\x89PNG")) {
            return null;
        }
        $info = @getimagesizefromstring($bin);
        if (!$info || $info[2] !== IMAGETYPE_PNG) {
            return null;
        }
        return 'data:image/png;base64,' . base64_encode($bin);
    }

    public static function storageDir(): string
    {
        $dir = CRM_ROOT . '/storage/contracts';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return $dir;
    }

    public static function countForClient(int $clientId): int
    {
        return (int)Database::value('SELECT COUNT(*) FROM contracts WHERE client_id = ?', [$clientId]);
    }
}
