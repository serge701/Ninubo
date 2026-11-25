<?php
declare(strict_types=1);

namespace Crm\Models;

use Crm\Core\Database;

final class Client
{
    public const STATUSES = ['lead' => 'Lead', 'active' => 'Active', 'inactive' => 'Inactive'];
    public const STATUS_COLORS = ['lead' => 'blue', 'active' => 'green', 'inactive' => 'secondary'];
    public const LANGUAGES = ['en' => 'English', 'es' => 'Spanish'];
    public const SOURCES = ['Referral', 'Website', 'Google', 'Facebook', 'Instagram', 'Walk-in', 'Cold call', 'Event', 'Other'];
    public const CONTACT_METHODS = ['phone' => 'Phone call', 'text' => 'Text message', 'whatsapp' => 'WhatsApp', 'email' => 'Email'];
    public const INDUSTRIES = [
        'Construction & Roofing', 'Home Services (HVAC, Plumbing, Electrical)', 'Landscaping & Pools', 'Automotive',
        'Medical & Dental', 'Health & Wellness', 'Legal', 'Accounting & Insurance', 'Real Estate', 'Restaurant & Food',
        'Retail', 'Beauty & Salon', 'Education', 'Manufacturing & Industrial', 'Logistics & Transportation',
        'Professional Services', 'Non-profit & Church', 'Other',
    ];
    public const PER_PAGE = 25;

    /** Editable columns and their defaults. */
    public const FIELDS = [
        'company_name' => '', 'contact_name' => '', 'contact_title' => '', 'email' => '', 'phone' => '',
        'alt_phone' => '', 'preferred_contact' => 'phone', 'website' => '', 'industry' => '', 'address1' => '', 'address2' => '', 'city' => '', 'state' => 'TX',
        'zip' => '', 'preferred_language' => 'en', 'status' => 'lead', 'source' => '', 'tax_exempt' => 0,
        'notes' => '', 'assigned_to' => null,
    ];

    private const SELECT = 'SELECT c.*, a.name AS assigned_name, cb.name AS created_by_name,
            (SELECT MAX(x.created_at) FROM client_activities x WHERE x.client_id = c.id) AS last_activity_at
        FROM clients c
        LEFT JOIN users a ON a.id = c.assigned_to
        LEFT JOIN users cb ON cb.id = c.created_by';

    public static function displayName(array $client): string
    {
        return trim((string)$client['company_name']) !== '' ? $client['company_name'] : $client['contact_name'];
    }

    public static function find(int $id): ?array
    {
        return Database::one(self::SELECT . ' WHERE c.id = ?', [$id]);
    }

    /**
     * @param array{q?:string,status?:string,assigned?:string} $filters  assigned: user id, "none" or ""
     * @return array{rows: array, total: int}
     */
    public static function search(array $filters, int $page = 1): array
    {
        $where = [];
        $params = [];
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $where[] = '(c.company_name LIKE ? OR c.contact_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR c.alt_phone LIKE ? OR c.city LIKE ? OR c.zip LIKE ?)';
            array_push($params, $like, $like, $like, $like, $like, $like, $like);
        }
        if (isset(self::STATUSES[$filters['status'] ?? ''])) {
            $where[] = 'c.status = ?';
            $params[] = $filters['status'];
        }
        $assigned = (string)($filters['assigned'] ?? '');
        if ($assigned === 'none') {
            $where[] = 'c.assigned_to IS NULL';
        } elseif (ctype_digit($assigned)) {
            $where[] = 'c.assigned_to = ?';
            $params[] = (int)$assigned;
        }
        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $total = (int)Database::value('SELECT COUNT(*) FROM clients c' . $whereSql, $params);
        $offset = max(0, ($page - 1) * self::PER_PAGE);
        $rows = Database::all(
            self::SELECT . $whereSql . ' ORDER BY c.updated_at DESC LIMIT ' . self::PER_PAGE . ' OFFSET ' . $offset,
            $params
        );
        return ['rows' => $rows, 'total' => $total];
    }

    /** id => display name, for selects. */
    public static function options(): array
    {
        $rows = Database::all("SELECT id, company_name, contact_name FROM clients WHERE status <> 'inactive' ORDER BY COALESCE(NULLIF(company_name, ''), contact_name)");
        $out = [];
        foreach ($rows as $r) {
            $out[(int)$r['id']] = self::displayName($r) . ($r['company_name'] !== '' ? ' — ' . $r['contact_name'] : '');
        }
        return $out;
    }

    /** @return array<string,int> */
    public static function counts(): array
    {
        $rows = Database::all('SELECT status, COUNT(*) AS n FROM clients GROUP BY status');
        return array_map('intval', array_merge(array_fill_keys(array_keys(self::STATUSES), 0), array_column($rows, 'n', 'status')));
    }

    public static function create(array $data, int $userId): int
    {
        return Database::insert('clients', $data + ['created_by' => $userId]);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('clients', $id, $data);
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM clients WHERE id = ?', [$id]);
    }

    /** Cleans posted input into column values. */
    public static function fromInput(array $in): array
    {
        $data = [];
        foreach (self::FIELDS as $key => $default) {
            $value = $in[$key] ?? $default;
            $data[$key] = is_string($value) ? trim($value) : $value;
        }
        $data['tax_exempt'] = !empty($in['tax_exempt']) ? 1 : 0;
        $data['state'] = strtoupper((string)$data['state']);
        if (!isset(self::LANGUAGES[$data['preferred_language']])) {
            $data['preferred_language'] = 'en';
        }
        if (!isset(self::CONTACT_METHODS[$data['preferred_contact']])) {
            $data['preferred_contact'] = 'phone';
        }
        if (!isset(self::STATUSES[$data['status']])) {
            $data['status'] = 'lead';
        }
        $data['assigned_to'] = ctype_digit((string)$data['assigned_to']) && User::find((int)$data['assigned_to'])
            ? (int)$data['assigned_to'] : null;
        if ($data['website'] !== '' && !preg_match('#^https?://#i', $data['website'])) {
            $data['website'] = 'https://' . $data['website'];
        }
        return $data;
    }

    /** @return array<string,string> field => error */
    public static function validate(array $data): array
    {
        $errors = [];
        if (mb_strlen($data['contact_name']) < 2) {
            $errors['contact_name'] = 'Enter the contact person\'s name.';
        }
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        if ($data['email'] === '' && $data['phone'] === '') {
            $errors['phone'] = 'Enter at least a phone number or an email.';
        }
        if ($data['website'] !== '' && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            $errors['website'] = 'Enter a valid website address.';
        }
        if ($data['zip'] !== '' && !preg_match('/^\d{5}(-\d{4})?$/', $data['zip'])) {
            $errors['zip'] = 'Use a 5-digit ZIP code.';
        }
        return $errors;
    }

    public static function addressLines(array $c): array
    {
        $cityLine = trim($c['city'] . ($c['state'] !== '' ? ', ' . $c['state'] : '') . ' ' . $c['zip'], ' ,');
        return array_values(array_filter([$c['address1'], $c['address2'], $cityLine], fn($l) => trim((string)$l) !== ''));
    }
}
