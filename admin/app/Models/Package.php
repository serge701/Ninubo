<?php
declare(strict_types=1);

namespace Crm\Models;

use Crm\Core\Database;
use Crm\Core\Settings;

/** Catalog of packages & services that are sold (and later invoiced / contracted). */
final class Package
{
    public const CATEGORIES = [
        'website' => 'Website package', 'addon' => 'Add-on service', 'maintenance' => 'Care plan (monthly)',
        'hosting' => 'Hosting', 'marketing' => 'Marketing / SEO', 'software' => 'Custom software',
        'data' => 'Data & BI', 'training' => 'Training', 'other' => 'Other',
    ];
    /** fixed = exact price; from = "From $X" (quoted per project); range = "$X – $Y" (suggested range). */
    public const PRICE_TYPES = ['fixed' => 'Fixed price', 'from' => 'Starting at ("From $…")', 'range' => 'Price range'];
    public const BILLING = ['one_time' => 'One-time', 'monthly' => 'Monthly', 'yearly' => 'Yearly'];
    public const BILLING_SUFFIX = ['one_time' => '', 'monthly' => '/mo', 'yearly' => '/yr'];

    /** Texas sales-tax treatment. data_processing uses the "taxable portion" from Settings (80%). */
    public static function taxTypes(): array
    {
        // 80 -> "80", 80.50 -> "80.5" (only trim decimals, never the integer part).
        $pct = (string)(float)Settings::get('sales_tax_taxable_pct');
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
        $money = function (mixed $v, bool $nullable): ?float {
            $v = str_replace([',', '$', ' '], '', (string)$v);
            if ($v === '' && $nullable) {
                return null;
            }
            return is_numeric($v) ? round((float)$v, 2) : -1;
        };
        $profile = (string)($in['brief_profile'] ?? '');
        $priceType = isset(self::PRICE_TYPES[$in['price_type'] ?? '']) ? $in['price_type'] : 'fixed';
        return [
            'name' => trim((string)($in['name'] ?? '')),
            'category' => isset(self::CATEGORIES[$in['category'] ?? '']) ? $in['category'] : 'other',
            'description' => trim((string)($in['description'] ?? '')),
            'price' => $money($in['price'] ?? '0', false),
            'price_type' => $priceType,
            'price_max' => $priceType === 'range' ? $money($in['price_max'] ?? '', true) : null,
            'internal_cost' => $money($in['internal_cost'] ?? '', true),
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
        if ($data['price_type'] === 'range' && ($data['price_max'] === null || $data['price_max'] <= $data['price'])) {
            $errors['price_max'] = 'Enter a maximum higher than the starting price.';
        }
        if ($data['internal_cost'] !== null && $data['internal_cost'] < 0) {
            $errors['internal_cost'] = 'Enter a valid amount or leave it blank.';
        }
        return $errors;
    }

    /** "$1,397", "$59/mo", "From $2,500", "$200 – $400". Whole dollars when there are no cents. */
    public static function priceLabel(array $p): string
    {
        $fmt = fn($n) => fmod((float)$n, 1.0) === 0.0 ? '$' . number_format((float)$n) : money($n);
        $suffix = self::BILLING_SUFFIX[$p['billing']] ?? '';
        return match ($p['price_type'] ?? 'fixed') {
            'from' => 'From ' . $fmt($p['price']) . $suffix,
            'range' => $fmt($p['price']) . ' – ' . $fmt($p['price_max']) . $suffix,
            default => $fmt($p['price']) . $suffix,
        };
    }

    /** Commercial margin over the internal production cost, or null when no cost is set. */
    public static function margin(array $p): ?array
    {
        if ($p['internal_cost'] === null || (float)$p['price'] <= 0) {
            return null;
        }
        $amount = (float)$p['price'] - (float)$p['internal_cost'];
        return ['amount' => $amount, 'pct' => $amount / (float)$p['price'] * 100];
    }
}
