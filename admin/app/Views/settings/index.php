<?php
/** @var array $s current settings */
$v = fn(string $k) => old($k, $s[$k] ?? '');

/** Renders a labelled text input bound to a setting. */
$input = function (string $key, string $label, array $opt = []) use ($v): string {
    $type = $opt['type'] ?? 'text';
    $col = $opt['col'] ?? 'col-md-6';
    $html = '<div class="' . $col . '"><label class="form-label' . (!empty($opt['required']) ? ' required' : '') . '" for="' . $key . '">' . e($label) . '</label>';
    if ($type === 'textarea') {
        $html .= '<textarea class="form-control' . invalid($key) . '" id="' . $key . '" name="' . $key . '" rows="' . ($opt['rows'] ?? 3) . '">' . e($v($key)) . '</textarea>';
    } else {
        $attrs = $type === 'number' ? ' step="' . ($opt['step'] ?? '1') . '" min="0"' : '';
        $input = '<input type="' . $type . '" class="form-control' . invalid($key) . '" id="' . $key . '" name="' . $key . '" value="' . e($v($key)) . '"' . $attrs . (!empty($opt['placeholder']) ? ' placeholder="' . e($opt['placeholder']) . '"' : '') . '>';
        $html .= !empty($opt['suffix'])
            ? '<div class="input-group">' . $input . '<span class="input-group-text">' . e($opt['suffix']) . '</span></div>'
            : $input;
    }
    if ($err = field_error($key)) {
        $html .= '<div class="invalid-feedback d-block">' . e($err) . '</div>';
    }
    if (!empty($opt['hint'])) {
        $html .= '<div class="form-hint">' . $opt['hint'] . '</div>';
    }
    return $html . '</div>';
};
?>
<form method="post" action="<?= url('/settings') ?>" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>

  <div class="card mb-3">
    <div class="card-header">
      <div>
        <h3 class="card-title">Business identity</h3>
        <p class="card-subtitle">Printed on every invoice and contract. Update it once the US business name is registered.</p>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <?= $input('business_name', 'Business name (shown on documents)', ['required' => true, 'hint' => 'Trade name / DBA, e.g. "Ninubo".']) ?>
        <?= $input('business_legal_name', 'Legal entity name', ['placeholder' => 'e.g. Ninubo LLC', 'hint' => 'Used as the contracting party in agreements. Leave blank to use the business name.']) ?>
        <?= $input('business_address1', 'Address line 1') ?>
        <?= $input('business_address2', 'Address line 2') ?>
        <?= $input('business_city', 'City', ['col' => 'col-md-4']) ?>
        <?= $input('business_state', 'State', ['col' => 'col-md-4']) ?>
        <?= $input('business_zip', 'ZIP code', ['col' => 'col-md-4']) ?>
        <?= $input('business_phone', 'Phone', ['type' => 'tel']) ?>
        <?= $input('business_email', 'Email', ['type' => 'email']) ?>
        <?= $input('business_website', 'Website') ?>
        <?= $input('business_ein', 'EIN (optional)', ['placeholder' => 'XX-XXXXXXX']) ?>
        <?= $input('business_signer_name', 'Authorized signer', ['hint' => 'Person who signs contracts for the business.']) ?>
        <?= $input('business_signer_title', 'Signer title', ['placeholder' => 'Owner']) ?>

        <div class="col-12">
          <label class="form-label" for="business_logo">Logo for documents</label>
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <?php if ($s['business_logo'] !== ''): ?>
              <div class="crm-logo-preview"><img src="<?= url('/settings/logo') ?>?v=<?= e(rawurlencode($s['business_logo'])) ?>" alt="Business logo"></div>
            <?php endif; ?>
            <div class="flex-fill" style="min-width:240px">
              <input type="file" class="form-control<?= invalid('business_logo') ?>" id="business_logo" name="business_logo" accept="image/png,image/jpeg,image/webp">
              <?php if ($err = field_error('business_logo')): ?><div class="invalid-feedback d-block"><?= e($err) ?></div><?php endif; ?>
              <div class="form-hint">PNG, JPG or WEBP up to 2 MB. Documents are printed on white, so use a dark or colored version of the logo.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">
      <div>
        <h3 class="card-title">Invoices &amp; Texas sales tax</h3>
        <p class="card-subtitle">All invoices are in US dollars.</p>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <?= $input('invoice_prefix', 'Invoice number prefix', ['col' => 'col-md-4', 'placeholder' => 'INV-']) ?>
        <?= $input('invoice_next_number', 'Next invoice number', ['col' => 'col-md-4', 'type' => 'number']) ?>
        <?= $input('invoice_due_days', 'Payment due (days)', ['col' => 'col-md-4', 'type' => 'number', 'suffix' => 'days']) ?>
        <?= $input('sales_tax_rate', 'Sales tax rate', ['col' => 'col-md-4', 'type' => 'number', 'step' => '0.001', 'suffix' => '%', 'hint' => 'Combined state + local rate. McAllen, Edinburg, Mission, Pharr, Brownsville: 8.25%.']) ?>
        <?= $input('sales_tax_taxable_pct', 'Taxable portion of taxable services', ['col' => 'col-md-4', 'type' => 'number', 'step' => '1', 'suffix' => '%', 'hint' => 'Texas taxes data processing services (web design, hosting, maintenance) on 80% of the charge. Each catalog item will be marked taxable or not.']) ?>
        <?= $input('texas_sales_tax_permit', 'Texas sales tax permit #', ['col' => 'col-md-4', 'hint' => 'Required before collecting sales tax.']) ?>
        <?= $input('invoice_payment_instructions', 'Payment instructions', ['col' => 'col-md-6', 'type' => 'textarea', 'rows' => 4, 'placeholder' => "Zelle: payments@...\nCheck payable to: ...\nBank transfer: ..."]) ?>
        <?= $input('invoice_footer', 'Invoice footer note', ['col' => 'col-md-6', 'type' => 'textarea', 'rows' => 4]) ?>
      </div>
      <div class="alert alert-info mt-3 mb-0 small">
        <i class="ti ti-info-circle me-1"></i>
        Tax defaults follow Texas Comptroller Rule 3.330 (data processing services, 80% taxable). Confirm the treatment of each service with your CPA.
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header"><h3 class="card-title">Contracts</h3></div>
    <div class="card-body">
      <div class="row g-3">
        <?= $input('contract_prefix', 'Contract number prefix', ['col' => 'col-md-4', 'placeholder' => 'AGR-']) ?>
        <?= $input('contract_next_number', 'Next contract number', ['col' => 'col-md-4', 'type' => 'number']) ?>
        <?= $input('contract_governing_county', 'Venue county (Texas)', ['col' => 'col-md-4', 'hint' => 'Courts where disputes are heard, e.g. Hidalgo or Cameron.']) ?>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end mb-3">
    <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Save settings</button>
  </div>
</form>

<?php if ($s['business_logo'] !== ''): ?>
<form method="post" action="<?= url('/settings/logo/delete') ?>" data-confirm="Remove the logo?" class="text-end">
  <?= csrf_field() ?>
  <button type="submit" class="btn btn-link link-danger btn-sm p-0">Remove current logo</button>
</form>
<?php endif; ?>
