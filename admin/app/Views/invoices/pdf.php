<?php
/**
 * Invoice PDF (Dompdf). Business identity comes from Settings ($s), never hard-coded.
 * @var array $inv @var array $client @var array $items @var array $payments @var array $schedule @var array $s @var ?string $logo
 */
use Crm\Core\Settings;
use Crm\Models\Invoice;

$status = Invoice::displayStatus($inv);
$watermark = ['draft' => 'DRAFT', 'void' => 'VOID', 'paid' => 'PAID'][$inv['status']] ?? null;
$billTo = $inv['bill_to'] ?: Invoice::billTo($client);
$businessLines = array_filter([
    $s['business_legal_name'] !== '' && $s['business_legal_name'] !== $s['business_name'] ? $s['business_legal_name'] : null,
    ...Settings::addressLines(),
    $s['business_phone'] ?: null,
    $s['business_email'] ?: null,
    $s['business_website'] ? preg_replace('#^https?://#', '', rtrim($s['business_website'], '/')) : null,
]);
$taxPct = rtrim(rtrim(number_format((float)$inv['tax_rate'], 3, '.', ''), '0'), '.');
$billingTag = ['monthly' => 'Monthly', 'yearly' => 'Yearly'];
$fmtQty = fn($q) => rtrim(rtrim(number_format((float)$q, 2, '.', ''), '0'), '.');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Invoice <?= e(Invoice::displayNumber($inv)) ?></title>
<style>
  @page { margin: 42px 46px 60px; }
  body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #1f2937; line-height: 1.45; }
  .muted { color: #6b7280; }
  .small { font-size: 8.5px; }
  table { width: 100%; border-collapse: collapse; }
  .head td { vertical-align: top; }
  .brand { font-size: 22px; font-weight: bold; color: #0c4a6e; letter-spacing: .5px; }
  .logo { max-height: 56px; max-width: 220px; }
  .doc-title { font-size: 26px; font-weight: bold; color: #0284c7; text-align: right; letter-spacing: 2px; }
  .meta { margin-top: 6px; }
  .meta td { padding: 1px 0; text-align: right; }
  .meta td.k { color: #6b7280; padding-right: 10px; }
  .parties { margin-top: 26px; }
  .parties td { vertical-align: top; width: 50%; }
  .label { font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #0284c7; margin-bottom: 4px; }
  .items { margin-top: 26px; }
  .items th { background: #0c4a6e; color: #fff; font-size: 8.5px; text-transform: uppercase; letter-spacing: .5px; padding: 7px 8px; text-align: left; }
  .items td { padding: 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
  .items .num { text-align: right; white-space: nowrap; }
  .items .desc { font-weight: bold; }
  .items .details { color: #6b7280; font-size: 8.5px; margin-top: 2px; }
  .tag { display: inline; font-size: 7.5px; font-weight: bold; color: #0369a1; background: #e0f2fe; padding: 1px 4px; border-radius: 3px; }
  .totals { margin-top: 12px; }
  .totals td { padding: 3px 8px; }
  .totals .k { text-align: right; color: #4b5563; }
  .totals .v { text-align: right; width: 110px; white-space: nowrap; }
  .totals .grand td { font-size: 13px; font-weight: bold; color: #0c4a6e; border-top: 2px solid #0c4a6e; padding-top: 6px; }
  .totals .due td { font-size: 12px; font-weight: bold; color: #b91c1c; }
  .box { margin-top: 22px; border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px 12px; }
  .sched td { padding: 3px 0; }
  .sched .amt { text-align: right; white-space: nowrap; width: 90px; }
  .sched .st { text-align: right; width: 70px; font-size: 8.5px; }
  .paid { color: #15803d; font-weight: bold; }
  .watermark { position: fixed; top: 300px; left: 80px; width: 560px; text-align: center; font-size: 110px; font-weight: bold;
               color: <?= $watermark === 'PAID' ? '#16a34a' : '#9ca3af' ?>; opacity: .12; transform: rotate(-30deg); }
  .footer { position: fixed; bottom: -40px; left: 0; right: 0; text-align: center; font-size: 8px; color: #9ca3af; }
</style>
</head>
<body>
<?php if ($watermark): ?><div class="watermark"><?= $watermark ?></div><?php endif; ?>
<div class="footer"><?= e($s['business_name']) ?><?= $s['texas_sales_tax_permit'] !== '' ? ' · Texas Sales Tax Permit ' . e($s['texas_sales_tax_permit']) : '' ?><?= $s['business_ein'] !== '' ? ' · EIN ' . e($s['business_ein']) : '' ?></div>

<table class="head">
  <tr>
    <td style="width:55%">
      <?php if ($logo): ?><img src="<?= $logo ?>" class="logo" alt=""><?php else: ?><div class="brand"><?= e($s['business_name']) ?></div><?php endif; ?>
    </td>
    <td style="width:45%">
      <div class="doc-title">INVOICE</div>
      <table class="meta">
        <tr><td class="k">Invoice #</td><td><strong><?= e($inv['number'] ?: 'DRAFT') ?></strong></td></tr>
        <tr><td class="k">Issue date</td><td><?= e(fmt_date($inv['issue_date'])) ?></td></tr>
        <tr><td class="k">Due date</td><td><?= e(fmt_date($inv['due_date'])) ?></td></tr>
        <?php if (!in_array($inv['status'], ['draft', 'void'], true)): ?>
        <tr><td class="k">Balance due</td><td><strong><?= e(money(Invoice::balance($inv))) ?></strong></td></tr>
        <?php endif; ?>
      </table>
    </td>
  </tr>
</table>

<table class="parties">
  <tr>
    <td>
      <div class="label">From</div>
      <strong><?= e($s['business_name']) ?></strong><br>
      <?php foreach ($businessLines as $line): ?><?= e($line) ?><br><?php endforeach; ?>
    </td>
    <td>
      <div class="label">Bill to</div>
      <?php foreach (explode("\n", $billTo) as $i => $line): ?><?= $i === 0 ? '<strong>' . e($line) . '</strong>' : e($line) ?><br><?php endforeach; ?>
    </td>
  </tr>
</table>

<table class="items">
  <thead>
    <tr><th>Description</th><th class="num" style="width:40px">Qty</th><th class="num" style="width:80px">Price</th><th class="num" style="width:90px">Amount</th></tr>
  </thead>
  <tbody>
  <?php foreach ($items as $item): ?>
    <tr>
      <td>
        <div class="desc"><?= e($item['description']) ?> <?php if (isset($billingTag[$item['billing']])): ?><span class="tag"><?= $billingTag[$item['billing']] ?></span><?php endif; ?></div>
        <?php if (trim((string)$item['details']) !== ''): ?><div class="details"><?= nl2br(e($item['details'])) ?></div><?php endif; ?>
      </td>
      <td class="num"><?= e($fmtQty($item['quantity'])) ?></td>
      <td class="num"><?= e(money($item['unit_price'])) ?></td>
      <td class="num"><?= e(money($item['line_total'])) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<table class="totals">
  <tr><td class="k">Subtotal</td><td class="v"><?= e(money($inv['subtotal'])) ?></td></tr>
  <?php if ((int)$inv['tax_exempt']): ?>
  <tr><td class="k">Sales tax (tax-exempt customer)</td><td class="v"><?= e(money(0)) ?></td></tr>
  <?php else: ?>
  <tr><td class="k small">Taxable amount<?= (float)$inv['taxable_amount'] < (float)$inv['subtotal'] ? ' (data processing services are taxed on ' . e(rtrim(rtrim(number_format((float)$inv['taxable_pct'], 2, '.', ''), '0'), '.')) . '% of the charge)' : '' ?></td><td class="v small"><?= e(money($inv['taxable_amount'])) ?></td></tr>
  <tr><td class="k">Texas sales tax (<?= e($taxPct) ?>%)</td><td class="v"><?= e(money($inv['tax_amount'])) ?></td></tr>
  <?php endif; ?>
  <tr class="grand"><td class="k" style="color:#0c4a6e">Total</td><td class="v"><?= e(money($inv['total'])) ?></td></tr>
  <?php if ((float)$inv['amount_paid'] > 0): ?>
  <tr><td class="k">Paid</td><td class="v paid">− <?= e(money($inv['amount_paid'])) ?></td></tr>
  <tr class="due"><td class="k">Balance due</td><td class="v"><?= e(money(Invoice::balance($inv))) ?></td></tr>
  <?php endif; ?>
</table>

<?php if ($inv['status'] !== 'void'): ?>
<div class="box">
  <div class="label">Payment schedule — <?= e(Invoice::TERMS[$inv['payment_terms']][0] ?? '') ?></div>
  <table class="sched">
    <?php foreach ($schedule as $step): ?>
    <tr>
      <td><strong><?= e($step['label']) ?> (<?= (int)$step['pct'] ?>%)</strong> <span class="muted">— <?= e($step['when']) ?></span></td>
      <td class="amt"><?= e(money($step['amount'])) ?></td>
      <td class="st"><?= $step['status'] === 'paid' ? '<span class="paid">PAID</span>' : ($step['status'] === 'partial' ? e(money($step['paid'])) . ' paid' : '') ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>

<?php if (trim($s['invoice_payment_instructions']) !== ''): ?>
<div class="box">
  <div class="label">How to pay</div>
  <?= nl2br(e($s['invoice_payment_instructions'])) ?>
</div>
<?php endif; ?>

<?php foreach ($briefs as $b): ?>
<div class="box">
  <div class="label">Your website content<?= count($briefs) > 1 ? ' — ' . e($b['package_name']) : '' ?></div>
  To start your website we need your information (texts, logo, photos, colors). Fill in this form — you can save
  and come back to it any time:<br>
  <a href="<?= e(\Crm\Models\Brief::publicLink($b)) ?>" style="color:#0c4a6e; word-break:break-all;"><?= e(\Crm\Models\Brief::publicLink($b)) ?></a>
</div>
<?php endforeach; ?>

<?php if (trim((string)$inv['notes']) !== ''): ?>
<div class="box">
  <div class="label">Notes</div>
  <?= nl2br(e($inv['notes'])) ?>
</div>
<?php endif; ?>

<?php if (trim($s['invoice_footer']) !== ''): ?>
<p style="margin-top:22px; text-align:center; color:#0c4a6e; font-weight:bold;"><?= e($s['invoice_footer']) ?></p>
<?php endif; ?>
</body>
</html>
