<?php
use Crm\Core\View;
use Crm\Models\Client;

$isNew = $contract === null;
$k = $contract ?? [
    'effective_date' => date('Y-m-d'),
    'client_signer_name' => $client['contact_name'],
    'client_signer_title' => $client['contact_title'],
    'client_signer_email' => $client['email'],
    'special_terms' => '',
];
$v = fn(string $key) => old($key, $k[$key] ?? '');
$invoiceOptions = [];
foreach ($invoices as $inv) {
    $invoiceOptions[(string)$inv['id']] = $inv['number'] . ' — ' . money($inv['total']) . ' (' . fmt_date($inv['issue_date']) . ')';
}
$selected = (string)old('invoice_id', $preselect ?: (array_key_first($invoiceOptions) ?? ''));
?>
<?php View::start('actions'); ?>
<a href="<?= url($isNew ? '/clients/' . (int)$client['id'] : '/contracts/' . (int)$contract['id']) ?>" class="btn"><i class="ti ti-arrow-left me-1"></i>Cancel</a>
<?php View::end(); ?>

<?php if (!$invoices): ?>
<div class="alert alert-warning">
  <i class="ti ti-alert-triangle me-1"></i>
  The contract is based on the client's invoice (scope, prices and payment schedule), and <?= e(Client::displayName($client)) ?> has no issued invoices yet.
  <a href="<?= url('/clients/' . (int)$client['id'] . '/invoices/new') ?>">Create and issue the invoice first</a>.
</div>
<?php endif; ?>

<div class="row row-cards">
  <div class="col-lg-8">
    <form class="card" method="post" action="<?= url($isNew ? '/contracts' : '/contracts/' . (int)$contract['id']) ?>" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="client_id" value="<?= (int)$client['id'] ?>">
      <div class="card-body">
        <div class="row g-3">
          <?= form_field('invoice_id', 'Related invoice', $selected, ['type' => 'select', 'col' => 'col-md-8', 'options' => $invoiceOptions, 'required' => true, 'hint' => 'Scope of work, prices, payment schedule and care plan come from this invoice.']) ?>
          <?= form_field('effective_date', 'Effective date', $v('effective_date'), ['type' => 'date', 'col' => 'col-md-4', 'required' => true]) ?>
          <div class="col-12"><hr class="my-1"><div class="fw-medium mt-2">Who signs for the client</div></div>
          <?= form_field('client_signer_name', 'Full name', $v('client_signer_name'), ['required' => true]) ?>
          <?= form_field('client_signer_title', 'Title', $v('client_signer_title'), ['placeholder' => 'e.g. Owner']) ?>
          <?= form_field('client_signer_email', 'Email', $v('client_signer_email'), ['type' => 'email', 'hint' => 'Where you will send the signing link.']) ?>
          <?= form_field('special_terms', 'Special terms (optional)', $v('special_terms'), ['type' => 'textarea', 'col' => 'col-12', 'rows' => 4, 'hint' => 'Added as the last section of the agreement — e.g. an agreed launch date or a discount condition. Leave blank if none.']) ?>
        </div>
      </div>
      <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary"<?= $invoices ? '' : ' disabled' ?>><i class="ti ti-device-floppy me-1"></i>Save draft and preview</button>
      </div>
    </form>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h3 class="card-title">What the agreement covers</h3></div>
      <div class="card-body small">
        <p>Website Design and Services Agreement, in English, under Texas law. It includes the scope and payment schedule from the invoice, client materials, revision rounds and change requests, timeline, domain &amp; hosting (first year + yearly renewal), the monthly care plan if the invoice has one, late payments, ownership, warranty, limitation of liability, termination, electronic signatures and venue.</p>
        <p class="mb-0 text-secondary">Day limits (cancellation notice, suspension, project pause, warranty) and the venue county are set in <a href="<?= url('/settings') ?>">Business settings</a>.</p>
      </div>
    </div>
  </div>
</div>
