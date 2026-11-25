<?php
declare(strict_types=1);

namespace Crm\Core;

/**
 * Business settings (key/value in the `settings` table), edited from Settings in the UI.
 * DEFAULTS defines every known key; a key missing in the DB falls back to its default.
 */
final class Settings
{
    public const DEFAULTS = [
        // Business identity (shown on invoices and contracts)
        'business_name' => 'NINUBO',
        'business_legal_name' => '',
        'business_address1' => '',
        'business_address2' => '',
        'business_city' => 'McAllen',
        'business_state' => 'TX',
        'business_zip' => '',
        'business_phone' => '',
        'business_email' => 'contacto@ninubo.com',
        'business_website' => 'https://ninubo.com',
        'business_ein' => '',
        'business_logo' => '',            // file name in admin/storage/uploads/
        'business_signer_name' => '',
        'business_signer_title' => 'Owner',

        // Invoicing
        'invoice_prefix' => 'INV-',
        'invoice_next_number' => '1001',
        'invoice_due_days' => '15',
        'sales_tax_rate' => '8.25',       // % — combined state + local rate where the service is delivered
        'sales_tax_taxable_pct' => '80',  // % of the charge that is taxable (Texas data processing services: 80%)
        'texas_sales_tax_permit' => '',
        'invoice_payment_instructions' => '',
        'invoice_footer' => 'Thank you for your business!',

        // Contracts
        'contract_prefix' => 'AGR-',
        'contract_next_number' => '1001',
        'contract_governing_county' => 'Hidalgo',
    ];

    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache === null) {
            $rows = Database::tableExists('settings') ? Database::all('SELECT setting_key, setting_value FROM settings') : [];
            $stored = array_column($rows, 'setting_value', 'setting_key');
            self::$cache = array_merge(self::DEFAULTS, array_intersect_key($stored, self::DEFAULTS));
        }
        return self::$cache;
    }

    public static function get(string $key): string
    {
        return (string)(self::all()[$key] ?? '');
    }

    /** Saves known keys only. */
    public static function save(array $values): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        foreach (array_intersect_key($values, self::DEFAULTS) as $key => $value) {
            $stmt->execute([$key, (string)$value]);
        }
        self::$cache = null;
    }

    /** One-line business address for documents. */
    public static function addressLines(): array
    {
        $s = self::all();
        $cityLine = trim($s['business_city'] . ($s['business_state'] !== '' ? ', ' . $s['business_state'] : '') . ' ' . $s['business_zip']);
        return array_values(array_filter([$s['business_address1'], $s['business_address2'], $cityLine], fn($l) => trim($l, ', ') !== ''));
    }
}
