<?php
declare(strict_types=1);

namespace Crm\Models;

use Crm\Core\Database;
use Crm\Core\Settings;

/**
 * Invoices. Lifecycle: draft (editable, no number) → issued as "sent" (gets its number, recurring services are
 * scheduled) → partial / paid as payments are recorded. Admins can void an issued invoice.
 * "Overdue" is not stored: it's an open invoice whose due date has passed (see displayStatus()).
 */
final class Invoice
{
    public const STATUSES = ['draft' => 'Draft', 'sent' => 'Open', 'partial' => 'Partially paid', 'paid' => 'Paid', 'void' => 'Void', 'overdue' => 'Overdue'];
    public const STATUS_COLORS = ['draft' => 'secondary', 'sent' => 'blue', 'partial' => 'yellow', 'paid' => 'green', 'void' => 'dark', 'overdue' => 'red'];
    public const PAYMENT_METHODS = ['zelle' => 'Zelle', 'check' => 'Check', 'cash' => 'Cash', 'card' => 'Credit / debit card', 'bank_transfer' => 'Bank transfer', 'other' => 'Other'];

    /** Payment terms from the commercial proposal. Each step: [label, percent, when]. */
    public const TERMS = [
        'full' => ['Payment in full', [['Payment in full', 100, 'Due by the due date']]],
        '50_50' => ['50% deposit / 50% before launch', [
            ['Deposit', 50, 'Due before work starts'],
            ['Balance', 50, 'Due before the site is published on its final domain'],
        ]],
        '40_30_30' => ['40% / 30% / 30%', [
            ['Deposit', 40, 'Due before work starts'],
            ['Progress payment', 30, 'Due at the agreed progress milestone'],
            ['Final payment', 30, 'Due at delivery, before launch'],
        ]],
    ];

    private const SELECT = 'SELECT i.*, c.company_name, c.contact_name, c.email AS client_email, u.name AS created_by_name
        FROM invoices i
        JOIN clients c ON c.id = i.client_id
        LEFT JOIN users u ON u.id = i.created_by';

    // ---------------------------------------------------------------- reading

    public static function find(int $id): ?array
    {
        return Database::one(self::SELECT . ' WHERE i.id = ?', [$id]);
    }

    public static function items(int $invoiceId): array
    {
        return Database::all('SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY sort_order, id', [$invoiceId]);
    }

    public static function payments(int $invoiceId): array
    {
        return Database::all(
            'SELECT p.*, u.name AS recorded_by_name FROM invoice_payments p LEFT JOIN users u ON u.id = p.recorded_by
             WHERE p.invoice_id = ? ORDER BY p.paid_on, p.id',
            [$invoiceId]
        );
    }

    public static function forClient(int $clientId): array
    {
        return Database::all(self::SELECT . ' WHERE i.client_id = ? ORDER BY i.issue_date DESC, i.id DESC', [$clientId]);
    }

    /**
     * @param array{status?:string,q?:string} $filters  status: draft|open|overdue|paid|void|''
     */
    public static function search(array $filters): array
    {
        $where = [];
        $params = [];
        switch ($filters['status'] ?? '') {
            case 'draft': $where[] = "i.status = 'draft'"; break;
            case 'open': $where[] = "i.status IN ('sent','partial')"; break;
            case 'overdue': $where[] = "i.status IN ('sent','partial') AND i.due_date < CURDATE()"; break;
            case 'paid': $where[] = "i.status = 'paid'"; break;
            case 'void': $where[] = "i.status = 'void'"; break;
        }
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $where[] = '(i.number LIKE ? OR c.company_name LIKE ? OR c.contact_name LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        $sql = self::SELECT . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY i.issue_date DESC, i.id DESC LIMIT 200';
        return Database::all($sql, $params);
    }

    /** Totals for the invoices page / dashboard. */
    public static function summary(): array
    {
        $row = Database::one(
            "SELECT
               COALESCE(SUM(CASE WHEN status IN ('sent','partial') THEN total - amount_paid END), 0) AS outstanding,
               COALESCE(SUM(CASE WHEN status IN ('sent','partial') AND due_date < CURDATE() THEN total - amount_paid END), 0) AS overdue_amount,
               COUNT(CASE WHEN status IN ('sent','partial') AND due_date < CURDATE() THEN 1 END) AS overdue_count,
               COUNT(CASE WHEN status IN ('sent','partial') THEN 1 END) AS open_count,
               COUNT(CASE WHEN status = 'draft' THEN 1 END) AS draft_count
             FROM invoices"
        );
        $row['paid_this_month'] = (float)Database::value(
            "SELECT COALESCE(SUM(p.amount), 0) FROM invoice_payments p JOIN invoices i ON i.id = p.invoice_id
             WHERE i.status <> 'void' AND p.paid_on >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        );
        return $row;
    }

    public static function displayStatus(array $inv): string
    {
        if (in_array($inv['status'], ['sent', 'partial'], true) && $inv['due_date'] < date('Y-m-d')) {
            return 'overdue';
        }
        return $inv['status'];
    }

    public static function displayNumber(array $inv): string
    {
        return $inv['number'] ?: 'Draft #' . $inv['id'];
    }

    public static function balance(array $inv): float
    {
        return round((float)$inv['total'] - (float)$inv['amount_paid'], 2);
    }

    /**
     * Payment schedule with how much of each installment is already covered by payments (applied in order).
     * @return list<array{label:string, pct:int, when:string, amount:float, paid:float, status:string}>
     */
    public static function schedule(array $inv): array
    {
        $steps = self::TERMS[$inv['payment_terms']][1] ?? self::TERMS['full'][1];
        $total = (float)$inv['total'];
        $remainingPaid = (float)$inv['amount_paid'];
        $out = [];
        $allocated = 0.0;
        foreach ($steps as $i => [$label, $pct, $when]) {
            // Last installment absorbs rounding so the parts always add up to the total.
            $amount = $i === count($steps) - 1 ? round($total - $allocated, 2) : round($total * $pct / 100, 2);
            $allocated += $amount;
            $paid = min($amount, max(0.0, $remainingPaid));
            $remainingPaid -= $paid;
            $status = $paid >= $amount - 0.005 ? 'paid' : ($paid > 0 ? 'partial' : 'due');
            $out[] = ['label' => $label, 'pct' => $pct, 'when' => $when, 'amount' => $amount, 'paid' => $paid, 'status' => $status];
        }
        return $out;
    }

    /** Suggested terms: recurring-only → full; over $2,000 → 40/30/30; otherwise 50/50. */
    public static function suggestedTerms(float $total, bool $hasOneTime): string
    {
        if (!$hasOneTime) {
            return 'full';
        }
        return $total > 2000 ? '40_30_30' : '50_50';
    }

    // ---------------------------------------------------------------- totals

    /**
     * Texas sales tax: data_processing lines are taxed on the taxable portion (80%), taxable lines on 100%,
     * exempt lines not at all; a tax-exempt client pays no tax.
     * @param list<array{quantity:float, unit_price:float, tax_type:string}> $items
     * @return array{subtotal:float, taxable_amount:float, tax_amount:float, total:float, items:array}
     */
    public static function computeTotals(array $items, bool $taxExempt, float $taxRate, float $taxablePct): array
    {
        $subtotal = 0.0;
        $taxable = 0.0;
        foreach ($items as $k => $item) {
            $line = round((float)$item['quantity'] * (float)$item['unit_price'], 2);
            $items[$k]['line_total'] = $line;
            $subtotal += $line;
            if (!$taxExempt) {
                $taxable += match ($item['tax_type']) {
                    'data_processing' => $line * $taxablePct / 100,
                    'taxable' => $line,
                    default => 0.0,
                };
            }
        }
        $taxable = round($taxable, 2);
        $tax = round($taxable * $taxRate / 100, 2);
        return [
            'subtotal' => round($subtotal, 2),
            'taxable_amount' => $taxable,
            'tax_amount' => $tax,
            'total' => round($subtotal + $tax, 2),
            'items' => $items,
        ];
    }

    // ---------------------------------------------------------------- writing

    /**
     * Cleans posted line items. Catalog lines keep the catalog's billing and tax treatment.
     * @return array{items: array, errors: array}
     */
    public static function itemsFromInput(array $rows): array
    {
        $items = [];
        $errors = [];
        foreach (array_values($rows) as $i => $row) {
            $description = trim((string)($row['description'] ?? ''));
            $price = str_replace([',', '$', ' '], '', (string)($row['unit_price'] ?? ''));
            $qty = str_replace([',', ' '], '', (string)($row['quantity'] ?? '1'));
            if ($description === '' && ($price === '' || (float)$price == 0.0)) {
                continue; // empty row
            }
            $package = !empty($row['package_id']) ? Package::find((int)$row['package_id']) : null;
            $item = [
                'package_id' => $package ? (int)$package['id'] : null,
                'subscription_id' => !empty($row['subscription_id']) ? (int)$row['subscription_id'] : null,
                'description' => mb_substr($description, 0, 190),
                'details' => trim((string)($row['details'] ?? '')),
                'quantity' => is_numeric($qty) ? round((float)$qty, 2) : 0,
                'unit_price' => is_numeric($price) ? round((float)$price, 2) : -1,
                'billing' => $package ? $package['billing'] : (in_array($row['billing'] ?? '', ['one_time', 'monthly', 'yearly'], true) ? $row['billing'] : 'one_time'),
                'tax_type' => $package ? $package['tax_type'] : (in_array($row['tax_type'] ?? '', ['data_processing', 'taxable', 'exempt'], true) ? $row['tax_type'] : 'data_processing'),
            ];
            $n = $i + 1;
            if ($item['description'] === '') {
                $errors[] = "Line {$n}: enter a description.";
            }
            if ($item['quantity'] <= 0) {
                $errors[] = "Line {$n}: quantity must be greater than 0.";
            }
            if ($item['unit_price'] < 0) {
                $errors[] = "Line {$n}: enter a valid price.";
            }
            $items[] = $item;
        }
        if (!$items) {
            $errors[] = 'Add at least one line to the invoice.';
        }
        return ['items' => $items, 'errors' => $errors];
    }

    /** Creates or updates a draft with its items. Returns the invoice id. */
    public static function saveDraft(?int $id, array $client, array $header, array $items, int $userId): int
    {
        $s = Settings::all();
        $taxRate = (float)$s['sales_tax_rate'];
        $taxablePct = (float)$s['sales_tax_taxable_pct'];
        $taxExempt = (bool)(int)$client['tax_exempt'];
        $totals = self::computeTotals($items, $taxExempt, $taxRate, $taxablePct);

        $row = [
            'issue_date' => $header['issue_date'],
            'due_date' => $header['due_date'],
            'payment_terms' => $header['payment_terms'],
            'notes' => $header['notes'],
            'internal_notes' => $header['internal_notes'],
            'subtotal' => $totals['subtotal'],
            'taxable_amount' => $totals['taxable_amount'],
            'tax_rate' => $taxRate,
            'taxable_pct' => $taxablePct,
            'tax_amount' => $totals['tax_amount'],
            'total' => $totals['total'],
            'tax_exempt' => $taxExempt ? 1 : 0,
        ];

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            if ($id === null) {
                $id = Database::insert('invoices', $row + ['client_id' => (int)$client['id'], 'status' => 'draft', 'created_by' => $userId]);
            } else {
                Database::update('invoices', $id, $row);
                Database::run('DELETE FROM invoice_items WHERE invoice_id = ?', [$id]);
            }
            foreach ($totals['items'] as $sort => $item) {
                Database::insert('invoice_items', $item + ['invoice_id' => $id, 'sort_order' => $sort]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return $id;
    }

    /**
     * Issues a draft: assigns the next invoice number, snapshots the bill-to block, schedules recurring services
     * and creates the website brief of each package that has one.
     * @return array{0: string, 1: string[]} the invoice number and timeline messages for what was set up
     */
    public static function issue(array $inv, int $userId): array
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            // Lock the counter row so two people issuing at once never get the same number.
            $next = Database::value("SELECT setting_value FROM settings WHERE setting_key = 'invoice_next_number' FOR UPDATE");
            $next = ctype_digit((string)$next) ? (int)$next : (int)Settings::get('invoice_next_number');
            do {
                $number = Settings::get('invoice_prefix') . $next;
                $taken = (bool)Database::value('SELECT COUNT(*) FROM invoices WHERE number = ?', [$number]);
                $next++;
            } while ($taken);
            Database::run(
                "INSERT INTO settings (setting_key, setting_value) VALUES ('invoice_next_number', ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                [(string)$next]
            );

            $client = Client::find((int)$inv['client_id']);
            Database::update('invoices', (int)$inv['id'], [
                'number' => $number,
                'status' => 'sent',
                'issued_at' => date('Y-m-d H:i:s'),
                'bill_to' => self::billTo($client),
            ]);

            $items = self::items((int)$inv['id']);
            $log = array_merge(
                Subscription::scheduleFromInvoice($inv, $items, $userId),
                Brief::createFromInvoice($inv, $number, $items, $userId)
            );
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return [$number, $log];
    }

    public static function billTo(array $client): string
    {
        $lines = [];
        if (trim((string)$client['company_name']) !== '') {
            $lines[] = $client['company_name'];
            $lines[] = 'Attn: ' . $client['contact_name'];
        } else {
            $lines[] = $client['contact_name'];
        }
        $lines = array_merge($lines, Client::addressLines($client));
        foreach (['email', 'phone'] as $k) {
            if (trim((string)$client[$k]) !== '') {
                $lines[] = $client[$k];
            }
        }
        return implode("\n", $lines);
    }

    public static function recordPayment(array $inv, array $payment, int $userId): void
    {
        Database::insert('invoice_payments', $payment + ['invoice_id' => (int)$inv['id'], 'recorded_by' => $userId]);
        self::refreshPaid((int)$inv['id']);
    }

    public static function deletePayment(int $paymentId): ?int
    {
        $invoiceId = Database::value('SELECT invoice_id FROM invoice_payments WHERE id = ?', [$paymentId]);
        if ($invoiceId === null) {
            return null;
        }
        Database::run('DELETE FROM invoice_payments WHERE id = ?', [$paymentId]);
        self::refreshPaid((int)$invoiceId);
        return (int)$invoiceId;
    }

    /** Recomputes amount_paid and the paid / partial / sent status. */
    public static function refreshPaid(int $invoiceId): void
    {
        $inv = self::find($invoiceId);
        if (!$inv || in_array($inv['status'], ['draft', 'void'], true)) {
            return;
        }
        $paid = round((float)Database::value('SELECT COALESCE(SUM(amount), 0) FROM invoice_payments WHERE invoice_id = ?', [$invoiceId]), 2);
        $status = $paid <= 0 ? 'sent' : ($paid + 0.005 >= (float)$inv['total'] ? 'paid' : 'partial');
        Database::update('invoices', $invoiceId, ['amount_paid' => $paid, 'status' => $status]);
    }

    /**
     * Voids an issued invoice and undoes its effect on recurring services: services it started are cancelled,
     * and services whose next period it billed go back one period. Briefs it created that the client never opened
     * are deleted. If a care plan it started is cancelled, the yearly domain & hosting renewal comes back.
     * @return string[] descriptions of what was changed
     */
    public static function void(int $id, string $reason, int $userId): array
    {
        $changed = [];
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $inv = self::find($id);
            Database::update('invoices', $id, ['status' => 'void', 'voided_at' => date('Y-m-d H:i:s'), 'void_reason' => mb_substr($reason, 0, 255)]);

            $careEnded = false;
            foreach (Database::all("SELECT * FROM client_subscriptions WHERE source_invoice_id = ? AND status = 'active'", [$id]) as $sub) {
                Subscription::update((int)$sub['id'], ['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s')]);
                $changed[] = $sub['description'] . ' (cancelled)';
                $careEnded = $careEnded || Subscription::isCarePlan($sub);
            }
            if ($careEnded && ($msg = Subscription::restoreRenewal((int)$inv['client_id'], $userId))) {
                $changed[] = $msg;
            }
            $changed = array_merge($changed, Brief::undoFromInvoice($id));
            $billed = Database::all(
                "SELECT s.* FROM invoice_items it JOIN client_subscriptions s ON s.id = it.subscription_id
                 WHERE it.invoice_id = ? AND (s.source_invoice_id IS NULL OR s.source_invoice_id <> ?) AND s.status = 'active'",
                [$id, $id]
            );
            foreach ($billed as $sub) {
                $back = date('Y-m-d', strtotime($sub['next_invoice_date'] . ($sub['billing'] === 'yearly' ? ' -1 year' : ' -1 month')));
                Subscription::update((int)$sub['id'], ['next_invoice_date' => $back]);
                $changed[] = $sub['description'] . ' (next invoice back to ' . date('M j, Y', strtotime($back)) . ')';
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return $changed;
    }

    public static function deleteDraft(int $id): void
    {
        Database::run("DELETE FROM invoices WHERE id = ? AND status = 'draft'", [$id]);
    }

    public static function countForClient(int $clientId): int
    {
        return (int)Database::value('SELECT COUNT(*) FROM invoices WHERE client_id = ?', [$clientId]);
    }
}
