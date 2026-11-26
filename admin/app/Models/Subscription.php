<?php
declare(strict_types=1);

namespace Crm\Models;

use Crm\Core\Database;
use Crm\Core\Settings;

/**
 * Recurring services per client (monthly care plans, yearly domain & hosting renewal).
 * Created when an invoice is issued; each "Create invoice" bills the next period and, once that invoice is issued,
 * moves next_invoice_date forward by one period.
 */
final class Subscription
{
    public const BILLING = ['monthly' => 'Monthly', 'yearly' => 'Yearly'];

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM client_subscriptions WHERE id = ?', [$id]);
    }

    public static function forClient(int $clientId): array
    {
        return Database::all(
            "SELECT * FROM client_subscriptions WHERE client_id = ? ORDER BY status = 'active' DESC, next_invoice_date",
            [$clientId]
        );
    }

    /** Active services whose next invoice is due within $days (or already late). */
    public static function dueWithin(int $days): array
    {
        return Database::all(
            "SELECT s.*, c.company_name, c.contact_name FROM client_subscriptions s JOIN clients c ON c.id = s.client_id
             WHERE s.status = 'active' AND s.next_invoice_date <= CURDATE() + INTERVAL ? DAY
             ORDER BY s.next_invoice_date",
            [$days]
        );
    }

    /** Monthly recurring revenue (yearly items counted as 1/12). */
    public static function mrr(): float
    {
        return (float)Database::value(
            "SELECT COALESCE(SUM(CASE billing WHEN 'monthly' THEN unit_price ELSE unit_price / 12 END), 0)
             FROM client_subscriptions WHERE status = 'active'"
        );
    }

    public static function addPeriod(string $date, string $billing): string
    {
        return date('Y-m-d', strtotime($date . ($billing === 'yearly' ? ' +1 year' : ' +1 month')));
    }

    /** "Dec 25, 2026 – Jan 24, 2027" for the period starting on $start. */
    public static function periodLabel(string $start, string $billing): string
    {
        $end = date('Y-m-d', strtotime(self::addPeriod($start, $billing) . ' -1 day'));
        return 'Service period ' . date('M j, Y', strtotime($start)) . ' – ' . date('M j, Y', strtotime($end));
    }

    /**
     * Runs inside Invoice::issue()'s transaction:
     *  - a recurring line without a subscription starts one (this invoice covers the first period);
     *  - a line that bills an existing subscription moves it to the next period;
     *  - a website package (one with a brief profile) schedules the yearly domain & hosting renewal, since the
     *    first year is included in the package.
     */
    public static function scheduleFromInvoice(array $inv, array $items, int $userId): void
    {
        $issueDate = $inv['issue_date'];
        $hasWebsitePackage = false;

        foreach ($items as $item) {
            if ($item['subscription_id']) {
                $sub = self::find((int)$item['subscription_id']);
                if ($sub && $sub['status'] === 'active') {
                    Database::update('client_subscriptions', (int)$sub['id'], [
                        'next_invoice_date' => self::addPeriod($sub['next_invoice_date'], $sub['billing']),
                    ]);
                }
                continue;
            }
            if (in_array($item['billing'], ['monthly', 'yearly'], true)) {
                // The first invoice's line says "… (first month)"; the service itself keeps the catalog name.
                $pkg = $item['package_id'] ? Package::find((int)$item['package_id']) : null;
                $subId = Database::insert('client_subscriptions', [
                    'client_id' => (int)$inv['client_id'],
                    'package_id' => $item['package_id'],
                    'description' => $pkg ? $pkg['name'] : preg_replace('/\s*\(first (month|year)\)$/i', '', $item['description']),
                    'details' => $pkg ? $pkg['description'] : $item['details'],
                    'unit_price' => $item['unit_price'],
                    'billing' => $item['billing'],
                    'tax_type' => $item['tax_type'],
                    'start_date' => $issueDate,
                    'next_invoice_date' => self::addPeriod($issueDate, $item['billing']),
                    'source_invoice_id' => (int)$inv['id'],
                    'created_by' => $userId,
                ]);
                Database::run('UPDATE invoice_items SET subscription_id = ? WHERE id = ?', [$subId, (int)$item['id']]);
            }
            if ($item['package_id'] && $item['billing'] === 'one_time') {
                $pkg = Package::find((int)$item['package_id']);
                if ($pkg && $pkg['brief_profile'] !== null && $pkg['category'] === 'website') {
                    $hasWebsitePackage = true;
                }
            }
        }

        if ($hasWebsitePackage) {
            self::scheduleRenewal((int)$inv['client_id'], $issueDate, (int)$inv['id'], $userId);
        }
    }

    /** Yearly domain & hosting renewal one year after the website invoice (skipped if the client already has it). */
    private static function scheduleRenewal(int $clientId, string $issueDate, int $invoiceId, int $userId): void
    {
        $renewal = Package::find((int)Settings::get('website_renewal_package_id'));
        if (!$renewal || !(int)$renewal['is_active']) {
            return;
        }
        $exists = (bool)Database::value(
            "SELECT COUNT(*) FROM client_subscriptions WHERE client_id = ? AND package_id = ? AND status = 'active'",
            [$clientId, (int)$renewal['id']]
        );
        if ($exists) {
            return;
        }
        Database::insert('client_subscriptions', [
            'client_id' => $clientId,
            'package_id' => (int)$renewal['id'],
            'description' => $renewal['name'],
            'details' => $renewal['description'],
            'unit_price' => $renewal['price'],
            'billing' => $renewal['billing'] === 'monthly' ? 'monthly' : 'yearly',
            'tax_type' => $renewal['tax_type'],
            'start_date' => self::addPeriod($issueDate, 'yearly'),
            'next_invoice_date' => self::addPeriod($issueDate, 'yearly'),
            'source_invoice_id' => $invoiceId,
            'created_by' => $userId,
        ]);
    }

    /** Line item that bills the subscription's next period (used to build the renewal draft invoice). */
    public static function nextLine(array $sub): array
    {
        return [
            'package_id' => $sub['package_id'],
            'subscription_id' => (int)$sub['id'],
            'description' => $sub['description'],
            'details' => self::periodLabel($sub['next_invoice_date'], $sub['billing']),
            'quantity' => 1,
            'unit_price' => (float)$sub['unit_price'],
            'billing' => $sub['billing'],
            'tax_type' => $sub['tax_type'],
        ];
    }

    /** Draft invoice already created for this subscription's next period (not issued yet), if any. */
    public static function pendingDraftId(int $subId): ?int
    {
        $id = Database::value(
            "SELECT i.id FROM invoices i JOIN invoice_items it ON it.invoice_id = i.id
             WHERE it.subscription_id = ? AND i.status = 'draft' LIMIT 1",
            [$subId]
        );
        return $id === null ? null : (int)$id;
    }

    public static function update(int $id, array $data): void
    {
        Database::update('client_subscriptions', $id, $data);
    }
}
