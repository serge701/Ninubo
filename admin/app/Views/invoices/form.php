<?php
use Crm\Core\Session;
use Crm\Core\Settings;
use Crm\Core\View;
use Crm\Models\Client;
use Crm\Models\Invoice;
use Crm\Models\Package;

$isNew = $invoice === null;
// After a validation error, re-show exactly what was posted.
$posted = Session::peekFlash('invoice_form');
if (is_array($posted)) {
    $header = array_merge($header, array_intersect_key($posted, array_flip(['issue_date', 'due_date', 'payment_terms', 'notes', 'internal_notes'])));
    $items = array_values(is_array($posted['items'] ?? null) ? $posted['items'] : []);
}

$catalogJs = array_map(fn($p) => [
    'id' => (int)$p['id'],
    'name' => $p['name'],
    'description' => (string)$p['description'],
    'price' => (float)$p['price'],
    'priceLabel' => Package::priceLabel($p),
    'billing' => $p['billing'],
    'taxType' => $p['tax_type'],
    'category' => $p['category'],
], $catalog);
$itemsJs = array_map(fn($i) => [
    'packageId' => $i['package_id'] ? (int)$i['package_id'] : null,
    'subscriptionId' => !empty($i['subscription_id']) ? (int)$i['subscription_id'] : null,
    'description' => (string)$i['description'],
    'details' => (string)($i['details'] ?? ''),
    'quantity' => (float)($i['quantity'] ?? 1),
    'unitPrice' => (float)str_replace([',', '$'], '', (string)($i['unit_price'] ?? 0)),
    'billing' => $i['billing'] ?? 'one_time',
    'taxType' => $i['tax_type'] ?? 'data_processing',
], $items);

$websitePackages = array_values(array_filter($catalog, fn($p) => $p['category'] === 'website' && $p['billing'] === 'one_time'));
$plans = array_values(array_filter($catalog, fn($p) => $p['billing'] === 'monthly'));
$others = array_values(array_filter($catalog, fn($p) => !in_array($p, $websitePackages, true) && !in_array($p, $plans, true)));

$config = [
    'catalog' => $catalogJs,
    'items' => $itemsJs,
    'taxRate' => (float)Settings::get('sales_tax_rate'),
    'taxablePct' => (float)Settings::get('sales_tax_taxable_pct'),
    'taxExempt' => (bool)(int)$client['tax_exempt'],
    'terms' => array_map(fn($t) => ['label' => $t[0], 'steps' => array_map(fn($s) => ['label' => $s[0], 'pct' => $s[1]], $t[1])], Invoice::TERMS),
    'termsChosen' => isset(Invoice::TERMS[$header['payment_terms'] ?? '']) && !$isNew,
];
$taxLabels = Package::taxTypes();
?>
<?php View::start('actions'); ?>
<a href="<?= url($isNew ? '/clients/' . (int)$client['id'] : '/invoices/' . (int)$invoice['id']) ?>" class="btn"><i class="ti ti-arrow-left me-1"></i>Cancel</a>
<?php View::end(); ?>

<form method="post" action="<?= url($isNew ? '/invoices' : '/invoices/' . (int)$invoice['id']) ?>" id="invoice-form" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="client_id" value="<?= (int)$client['id'] ?>">

  <div class="row row-cards">
    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-header"><h3 class="card-title"><i class="ti ti-world me-2"></i>Website package</h3></div>
        <div class="card-body">
          <div class="form-selectgroup form-selectgroup-boxes d-flex flex-wrap gap-2" data-picker="website">
            <label class="form-selectgroup-item flex-fill">
              <input type="radio" name="_pick_website" value="" class="form-selectgroup-input">
              <div class="form-selectgroup-label p-3 h-100"><div class="fw-medium">None</div><div class="small text-secondary">No website package</div></div>
            </label>
            <?php foreach ($websitePackages as $p): ?>
            <label class="form-selectgroup-item flex-fill">
              <input type="radio" name="_pick_website" value="<?= (int)$p['id'] ?>" class="form-selectgroup-input">
              <div class="form-selectgroup-label p-3 h-100">
                <div class="fw-medium"><?= e($p['name']) ?></div>
                <div class="small text-secondary"><?= e(Package::priceLabel($p)) ?></div>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
          <div class="form-hint mt-2">Domain and hosting are included for the first year; the yearly renewal is scheduled automatically when the invoice is issued.</div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><h3 class="card-title"><i class="ti ti-heart-handshake me-2"></i>Monthly care plan</h3></div>
        <div class="card-body">
          <div class="form-selectgroup form-selectgroup-boxes d-flex flex-wrap gap-2" data-picker="plan">
            <label class="form-selectgroup-item flex-fill">
              <input type="radio" name="_pick_plan" value="" class="form-selectgroup-input">
              <div class="form-selectgroup-label p-3 h-100"><div class="fw-medium">None</div><div class="small text-secondary">No monthly plan</div></div>
            </label>
            <?php foreach ($plans as $p): ?>
            <label class="form-selectgroup-item flex-fill">
              <input type="radio" name="_pick_plan" value="<?= (int)$p['id'] ?>" class="form-selectgroup-input">
              <div class="form-selectgroup-label p-3 h-100">
                <div class="fw-medium"><?= e($p['name']) ?></div>
                <div class="small text-secondary"><?= e(Package::priceLabel($p)) ?></div>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
          <div class="form-hint mt-2">This invoice charges the first month. The plan is then billed monthly from the client's page (Recurring services).</div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">
          <h3 class="card-title"><i class="ti ti-list-details me-2"></i>Line items</h3>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table crm-lines">
            <thead>
              <tr><th>Description</th><th class="text-end">Qty</th><th class="text-end">Price</th><th class="text-end">Amount</th><th></th></tr>
            </thead>
            <tbody id="invoice-lines"></tbody>
          </table>
        </div>
        <div class="card-body border-top d-flex flex-wrap gap-2 align-items-center">
          <select class="form-select w-auto flex-fill" id="add-catalog" aria-label="Add from catalog">
            <option value="">Add from catalog…</option>
            <?php foreach (['Add-ons & other services' => $others, 'Website packages' => $websitePackages, 'Care plans' => $plans] as $label => $group): if (!$group) continue; ?>
            <optgroup label="<?= e($label) ?>">
              <?php foreach ($group as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['name'] . ' — ' . Package::priceLabel($p)) ?></option><?php endforeach; ?>
            </optgroup>
            <?php endforeach; ?>
          </select>
          <button type="button" class="btn" id="add-custom"><i class="ti ti-plus me-1"></i>Custom line</button>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <div class="row g-3">
            <?= form_field('notes', 'Notes for the client', (string)($header['notes'] ?? ''), ['type' => 'textarea', 'col' => 'col-md-6', 'rows' => 3, 'hint' => 'Printed on the invoice.']) ?>
            <?= form_field('internal_notes', 'Internal notes', (string)($header['internal_notes'] ?? ''), ['type' => 'textarea', 'col' => 'col-md-6', 'rows' => 3, 'hint' => 'Only visible in the CRM.']) ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Bill to</h3></div>
        <div class="card-body">
          <div class="fw-medium"><?= e(Client::displayName($client)) ?></div>
          <?php if ($client['company_name'] !== ''): ?><div class="text-secondary small"><?= e($client['contact_name']) ?></div><?php endif; ?>
          <div class="text-secondary small"><?= implode('<br>', array_map('e', Client::addressLines($client))) ?></div>
          <?php if ((int)$client['tax_exempt']): ?><span class="badge bg-orange-lt mt-2">Tax exempt — no sales tax</span><?php endif; ?>
          <?php if (!Client::addressLines($client)): ?><div class="small text-warning mt-2"><i class="ti ti-alert-triangle"></i> No address on file. <a href="<?= url('/clients/' . (int)$client['id'] . '/edit') ?>">Add it</a> before issuing.</div><?php endif; ?>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <div class="row g-3">
            <?= form_field('issue_date', 'Issue date', (string)$header['issue_date'], ['type' => 'date', 'col' => 'col-6', 'required' => true]) ?>
            <?= form_field('due_date', 'Due date', (string)$header['due_date'], ['type' => 'date', 'col' => 'col-6', 'required' => true]) ?>
            <?= form_field('payment_terms', 'Payment terms', (string)($header['payment_terms'] ?? ''), ['type' => 'select', 'col' => 'col-12', 'options' => array_map(fn($t) => $t[0], Invoice::TERMS), 'hint' => 'Suggested automatically: 50/50, or 40/30/30 over $2,000, or full payment for plan-only invoices.']) ?>
          </div>
        </div>
      </div>

      <div class="card mb-3 crm-totals">
        <div class="card-body">
          <div class="d-flex justify-content-between"><span>Subtotal</span><span id="t-subtotal">$0.00</span></div>
          <div class="d-flex justify-content-between small text-secondary" id="t-taxable-row"><span>Taxable amount</span><span id="t-taxable">$0.00</span></div>
          <div class="d-flex justify-content-between"><span>Sales tax (<span id="t-rate"></span>%)</span><span id="t-tax">$0.00</span></div>
          <hr class="my-2">
          <div class="d-flex justify-content-between fs-3 fw-bold"><span>Total</span><span id="t-total">$0.00</span></div>
          <div id="t-schedule" class="mt-3 small"></div>
        </div>
      </div>

      <div class="d-grid">
        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Save draft</button>
      </div>
      <div class="form-hint mt-2 text-center">The invoice number is assigned when you issue it.</div>
    </div>
  </div>
</form>

<template id="line-template">
  <tr class="crm-line">
    <td>
      <input type="hidden" data-f="package_id"><input type="hidden" data-f="subscription_id">
      <input type="hidden" data-f="billing"><input type="hidden" data-f="tax_type">
      <input type="text" class="form-control form-control-sm fw-medium" data-f="description" placeholder="Description" maxlength="190">
      <textarea class="form-control form-control-sm mt-1 text-secondary" data-f="details" rows="1" placeholder="Details (optional)"></textarea>
      <div class="mt-1 small d-flex flex-wrap gap-2 align-items-center">
        <span class="badge bg-secondary-lt" data-billing-badge></span>
        <span class="text-secondary" data-tax-label></span>
        <select class="form-select form-select-sm w-auto d-none" data-tax-select aria-label="Sales tax">
          <?php foreach ($taxLabels as $k => $label): ?><option value="<?= e($k) ?>"><?= e($label) ?></option><?php endforeach; ?>
        </select>
        <span class="text-secondary" data-range-hint></span>
      </div>
    </td>
    <td><input type="number" class="form-control form-control-sm text-end" data-f="quantity" min="0.01" step="0.01" style="width:4.5rem"></td>
    <td><div class="input-group input-group-sm flex-nowrap" style="width:8.5rem"><span class="input-group-text">$</span><input type="number" class="form-control text-end" data-f="unit_price" min="0" step="0.01"></div></td>
    <td class="text-end fw-medium" data-amount>$0.00</td>
    <td><button type="button" class="btn btn-sm btn-ghost-danger px-2" data-remove title="Remove line"><i class="ti ti-x"></i></button></td>
  </tr>
</template>

<script>window.CRM_INVOICE = <?= json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= asset('app/invoice-form.js') ?>"></script>
