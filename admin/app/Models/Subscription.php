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
     *    first year is included in the package — unless the client has a care plan (maintenance category),
     *    which already includes hosting; starting a care plan cancels a renewal that was scheduled.
     * @return string[] timeline messages
     */
    public static function scheduleFromInvoice(array $inv, array $items, int $userId): array
    {
        $log = [];
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

        $clientId = (int)$inv['client_id'];
        $care = self::activeCarePlan($clientId);
        if ($care) {
            foreach (self::cancelRenewals($clientId) as $desc) {
                $log[] = '"' . $desc . '" cancelled: hosting is included in the care plan "' . $care['description'] . '".';
            }
            if ($hasWebsitePackage) {
                $log[] = 'No yearly domain & hosting renewal scheduled: hosting is included in the care plan "' . $care['description'] . '".';
            }
        } elseif ($hasWebsitePackage && ($msg = self::scheduleRenewal($clientId, self::addPeriod($issueDate, 'yearly'), (int)$inv['id'], $userId))) {
            $log[] = $msg . '.';
        }
        return $log;
    }

    /** The client's active care plan (a recurring service from a "maintenance" catalog item), if any. */
    public static function activeCarePlan(int $clientId): ?array
    {
        return Database::one(
            "SELECT s.* FROM client_subscriptions s JOIN packages p ON p.id = s.package_id
             WHERE s.client_id = ? AND s.status = 'active' AND p.category = 'maintenance'
             ORDER BY s.start_date LIMIT 1",
            [$clientId]
        );
    }

    public static function isCarePlan(array $sub): bool
    {
        $pkg = $sub['package_id'] ? Package::find((int)$sub['package_id']) : null;
        return $pkg !== null && $pkg['category'] === 'maintenance';
    }

    /** Cancels the client's active domain & hosting renewals. @return string[] their descriptions */
    private static function cancelRenewals(int $clientId): array
    {
        $renewalId = (int)Settings::get('website_renewal_package_id');
        $subs = Database::all(
            "SELECT * FROM client_subscriptions WHERE client_id = ? AND package_id = ? AND status = 'active'",
            [$clientId, $renewalId]
        );
        foreach ($subs as $sub) {
            self::update((int)$sub['id'], ['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s')]);
        }
        return array_column($subs, 'description');
    }

    /**
     * After a care plan ends (cancelled, or its invoice voided): if the client has a website and no other care plan,
     * the yearly domain & hosting renewal comes back, on the next anniversary of the website invoice.
     * @return string|null timeline message
     */
    public static function restoreRenewal(int $clientId, int $userId): ?string
    {
        if (self::activeCarePlan($clientId)) {
            return null;
        }
        $website = Database::one(
            "SELECT i.id, i.issue_date FROM invoices i
             JOIN invoice_items it ON it.invoice_id = i.id
             JOIN packages p ON p.id = it.package_id
             WHERE i.client_id = ? AND i.status NOT IN ('draft', 'void')
               AND it.billing = 'one_time' AND p.category = 'website' AND p.brief_profile IS NOT NULL
             ORDER BY i.issue_date LIMIT 1",
            [$clientId]
        );
        if (!$website) {
            return null;
        }
        $next = self::addPeriod($website['issue_date'], 'yearly');
        while ($next < date('Y-m-d')) {
            $next = self::addPeriod($next, 'yearly');
        }
        $msg = self::scheduleRenewal($clientId, $next, (int)$website['id'], $userId);
        return $msg ? $msg . ' (no care plan includes hosting any more)' : null;
    }

    /**
     * Yearly domain & hosting renewal starting on $date (skipped if the client already has it).
     * @return string|null timeline message when it was scheduled
     */
    private static function scheduleRenewal(int $clientId, string $date, int $invoiceId, int $userId): ?string
    {
        $renewal = Package::find((int)Settings::get('website_renewal_package_id'));
        if (!$renewal || !(int)$renewal['is_active']) {
            return null;
        }
        $exists = (bool)Database::value(
            "SELECT COUNT(*) FROM client_subscriptions WHERE client_id = ? AND package_id = ? AND status = 'active'",
            [$clientId, (int)$renewal['id']]
        );
        if ($exists) {
            return null;
        }
        Database::insert('client_subscriptions', [
            'client_id' => $clientId,
            'package_id' => (int)$renewal['id'],
            'description' => $renewal['name'],
            'details' => $renewal['description'],
            'unit_price' => $renewal['price'],
            'billing' => $renewal['billing'] === 'monthly' ? 'monthly' : 'yearly',
            'tax_type' => $renewal['tax_type'],
            'start_date' => $date,
            'next_invoice_date' => $date,
            'source_invoice_id' => $invoiceId,
            'created_by' => $userId,
        ]);
        return '"' . $renewal['name'] . '" scheduled for ' . fmt_date($date);
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
