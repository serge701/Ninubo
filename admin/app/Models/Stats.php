<?php
declare(strict_types=1);

namespace Crm\Models;

use Crm\Core\Database;

/**
 * Dashboard KPIs. "Sales" = total of issued, non-void invoices (by issue date).
 * "Collected" = payments received on non-void invoices (by payment date).
 */
final class Stats
{
    private const ISSUED = "i.status IN ('sent','partial','paid')";

    /** Sales and collections for a month offset (0 = this month, -1 = last month). */
    public static function month(int $offset = 0): array
    {
        $start = date('Y-m-01', strtotime("first day of {$offset} month"));
        $end = date('Y-m-01', strtotime($start . ' +1 month'));
        $sales = Database::one(
            'SELECT COALESCE(SUM(i.total), 0) AS total, COUNT(*) AS n FROM invoices i
             WHERE ' . self::ISSUED . ' AND i.issue_date >= ? AND i.issue_date < ?',
            [$start, $end]
        );
        $collected = (float)Database::value(
            "SELECT COALESCE(SUM(p.amount), 0) FROM invoice_payments p JOIN invoices i ON i.id = p.invoice_id
             WHERE i.status <> 'void' AND p.paid_on >= ? AND p.paid_on < ?",
            [$start, $end]
        );
        return ['sales' => (float)$sales['total'], 'sales_count' => (int)$sales['n'], 'collected' => $collected];
    }

    /**
     * Last $months months (oldest first): invoiced and collected per month.
     * @return list<array{key:string, label:string, short:string, invoiced:float, collected:float}>
     */
    public static function monthly(int $months = 12): array
    {
        $first = date('Y-m-01', strtotime('first day of -' . ($months - 1) . ' month'));
        $invoiced = array_column(Database::all(
            "SELECT DATE_FORMAT(i.issue_date, '%Y-%m') AS ym, SUM(i.total) AS v FROM invoices i
             WHERE " . self::ISSUED . " AND i.issue_date >= ? GROUP BY ym",
            [$first]
        ), 'v', 'ym');
        $collected = array_column(Database::all(
            "SELECT DATE_FORMAT(p.paid_on, '%Y-%m') AS ym, SUM(p.amount) AS v FROM invoice_payments p
             JOIN invoices i ON i.id = p.invoice_id WHERE i.status <> 'void' AND p.paid_on >= ? GROUP BY ym",
            [$first]
        ), 'v', 'ym');

        $out = [];
        for ($k = 0; $k < $months; $k++) {
            $ts = strtotime($first . " +{$k} month");
            $ym = date('Y-m', $ts);
            $out[] = [
                'key' => $ym,
                'label' => date('F Y', $ts),
                'short' => date('M', $ts),
                'invoiced' => (float)($invoiced[$ym] ?? 0),
                'collected' => (float)($collected[$ym] ?? 0),
            ];
        }
        return $out;
    }

    /** Sales this year by the user who created the invoice. */
    public static function salesByRep(): array
    {
        return Database::all(
            'SELECT COALESCE(u.name, \'Unknown\') AS name, SUM(i.total) AS total, COUNT(*) AS n
             FROM invoices i LEFT JOIN users u ON u.id = i.created_by
             WHERE ' . self::ISSUED . ' AND YEAR(i.issue_date) = YEAR(CURDATE())
             GROUP BY i.created_by, u.name ORDER BY total DESC'
        );
    }

    /** Website packages sold this year (every active website package listed, even at zero). */
    public static function packagesSold(): array
    {
        return Database::all(
            "SELECT p.name, COUNT(it.id) AS n, COALESCE(SUM(it.line_total), 0) AS revenue
             FROM packages p
             LEFT JOIN invoice_items it ON it.package_id = p.id AND it.billing = 'one_time'
               AND it.invoice_id IN (SELECT i.id FROM invoices i WHERE " . self::ISSUED . " AND YEAR(i.issue_date) = YEAR(CURDATE()))
             WHERE p.category = 'website' AND p.billing = 'one_time' AND (p.is_active = 1 OR it.id IS NOT NULL)
             GROUP BY p.id, p.name, p.sort_order ORDER BY n DESC, p.sort_order"
        );
    }

    /** Open balances by how late they are. */
    public static function aging(): array
    {
        $row = Database::one(
            "SELECT
               COALESCE(SUM(CASE WHEN due_date >= CURDATE() THEN total - amount_paid END), 0) AS b0,
               COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 1 AND 30 THEN total - amount_paid END), 0) AS b1,
               COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 31 AND 60 THEN total - amount_paid END), 0) AS b2,
               COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) > 60 THEN total - amount_paid END), 0) AS b3,
               COUNT(CASE WHEN due_date >= CURDATE() THEN 1 END) AS n0,
               COUNT(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 1 AND 30 THEN 1 END) AS n1,
               COUNT(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 31 AND 60 THEN 1 END) AS n2,
               COUNT(CASE WHEN DATEDIFF(CURDATE(), due_date) > 60 THEN 1 END) AS n3
             FROM invoices WHERE status IN ('sent','partial')"
        );
        $labels = ['Not due yet', '1–30 days late', '31–60 days late', 'Over 60 days late'];
        $out = [];
        foreach ($labels as $i => $label) {
            $out[] = ['label' => $label, 'amount' => (float)$row["b{$i}"], 'count' => (int)$row["n{$i}"]];
        }
        return $out;
    }

    /** Lead → active conversion for clients created in the last $days days. */
    public static function conversion(int $days = 90): array
    {
        $row = Database::one(
            "SELECT COUNT(*) AS created, COUNT(CASE WHEN status = 'active' THEN 1 END) AS won
             FROM clients WHERE created_at >= NOW() - INTERVAL ? DAY",
            [$days]
        );
        $created = (int)$row['created'];
        return ['created' => $created, 'won' => (int)$row['won'], 'rate' => $created ? (int)$row['won'] / $created * 100 : null];
    }

    public static function newClientsThisMonth(): int
    {
        return (int)Database::value("SELECT COUNT(*) FROM clients WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
    }

    /** Active recurring services, for the MRR tile. */
    public static function activePlans(): int
    {
        return (int)Database::value("SELECT COUNT(*) FROM client_subscriptions WHERE status = 'active' AND billing = 'monthly'");
    }
}
