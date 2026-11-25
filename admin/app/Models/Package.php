<?php
declare(strict_types=1);

namespace Crm\Models;

use Crm\Core\Database;
use Crm\Core\Settings;

/** Catalog of packages & services that are sold (and later invoiced / contracted). */
final class Package
{
    public const CATEGORIES = [
        'website' => 'Website', 'hosting' => 'Hosting', 'maintenance' => 'Maintenance', 'software' => 'Custom software',
        'data' => 'Data & BI', 'marketing' => 'Marketing / SEO', 'training' => 'Training', 'other' => 'Other',
    ];
    public const BILLING = ['one_time' => 'One-time', 'monthly' => 'Monthly', 'yearly' => 'Yearly'];
    public const BILLING_SUFFIX = ['one_time' => '', 'monthly' => '/mo', 'yearly' => '/yr'];

    /** Texas sales-tax treatment. data_processing uses the "taxable portion" from Settings (80%). */
    public static function taxTypes(): array
    {
        $pct = rtrim(rtrim(Settings::get('sales_tax_taxable_pct'), '0'), '.');
        return [
            'data_processing' => "Data processing — taxed on {$pct}%",
            'taxable' => 'Taxable — 100%',
            'exempt' => 'Not taxable',
        ];
    }

    public static function all(bool $activeOnly = false): array
    {
        return Database::all(
            'SELECT p.*, (SELECT COUNT(*) FROM client_briefs b WHERE b.package_id = p.id) AS brief_count FROM packages p'
            . ($activeOnly ? ' WHERE p.is_active = 1' : '') . ' ORDER BY p.is_active DESC, p.sort_order, p.name'
        );
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM packages WHERE id = ?', [$id]);
    }

    /** Active packages that come with a website brief. */
    public static function withBrief(): array
    {
        return Database::all('SELECT * FROM packages WHERE is_active = 1 AND brief_profile IS NOT NULL ORDER BY sort_order, name');
    }

    public static function create(array $data): int
    {
        return Database::insert('packages', $data);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('packages', $id, $data);
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM packages WHERE id = ?', [$id]);
    }

    public static function isUsed(int $id): bool
    {
        return (bool)Database::value('SELECT COUNT(*) FROM client_briefs WHERE package_id = ?', [$id]);
    }

    public static function fromInput(array $in): array
    {
        $price = str_replace([',', '$', ' '], '', (string)($in['price'] ?? '0'));
        $profile = (string)($in['brief_profile'] ?? '');
        return [
            'name' => trim((string)($in['name'] ?? '')),
            'category' => isset(self::CATEGORIES[$in['category'] ?? '']) ? $in['category'] : 'other',
            'description' => trim((string)($in['description'] ?? '')),
            'price' => is_numeric($price) ? round((float)$price, 2) : -1,
            'billing' => isset(self::BILLING[$in['billing'] ?? '']) ? $in['billing'] : 'one_time',
            'tax_type' => array_key_exists($in['tax_type'] ?? '', self::taxTypes()) ? $in['tax_type'] : 'data_processing',
            'brief_profile' => isset(ninubo_brief_profiles()[$profile]) ? $profile : null,
            'is_active' => !empty($in['is_active']) ? 1 : 0,
            'sort_order' => (int)($in['sort_order'] ?? 0),
        ];
    }

    public static function validate(array $data): array
    {
        $errors = [];
        if (mb_strlen($data['name']) < 2) {
            $errors['name'] = 'Enter a name.';
        }
        if ($data['price'] < 0 || $data['price'] > 9999999) {
            $errors['price'] = 'Enter a valid price.';
        }
        return $errors;
    }

    public static function priceLabel(array $p): string
    {
        return money($p['price']) . (self::BILLING_SUFFIX[$p['billing']] ?? '');
    }
}
