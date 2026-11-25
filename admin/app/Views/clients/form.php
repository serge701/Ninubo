<?php
use Crm\Core\View;
use Crm\Models\Client;

$isNew = $client === null;
$c = $client ?? Client::FIELDS;
$v = fn(string $k) => old($k, $c[$k] ?? '');

$userOptions = [];
foreach ($users as $u) {
    $userOptions[(string)$u['id']] = $u['name'];
}
$assigned = old('assigned_to', $isNew ? (string)auth_user()['id'] : (string)($c['assigned_to'] ?? ''));
$industries = array_combine(Client::INDUSTRIES, Client::INDUSTRIES);
if ($v('industry') !== '' && !isset($industries[$v('industry')])) {
    $industries[$v('industry')] = $v('industry');
}
$sources = array_combine(Client::SOURCES, Client::SOURCES);
?>
<?php View::start('actions'); ?>
<a href="<?= url($isNew ? '/clients' : '/clients/' . (int)$c['id']) ?>" class="btn"><i class="ti ti-arrow-left me-1"></i>Cancel</a>
<?php View::end(); ?>

<form method="post" action="<?= url($isNew ? '/clients' : '/clients/' . (int)$c['id']) ?>" novalidate>
  <?= csrf_field() ?>
  <div class="row row-cards">
    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Contact</h3></div>
        <div class="card-body">
          <div class="row g-3">
            <?= form_field('company_name', 'Company / business name', $v('company_name'), ['hint' => 'Leave blank if the client is an individual.']) ?>
            <?= form_field('industry', 'Industry', $v('industry'), ['type' => 'select', 'options' => $industries, 'empty' => '—']) ?>
            <?= form_field('contact_name', 'Contact person', $v('contact_name'), ['required' => true, 'placeholder' => 'First and last name']) ?>
            <?= form_field('contact_title', 'Title / role', $v('contact_title'), ['type' => 'select', 'options' => array_combine(Client::CONTACT_TITLES, Client::CONTACT_TITLES), 'empty' => '—']) ?>
            <?= form_field('phone', 'Phone', $v('phone'), ['type' => 'tel']) ?>
            <?= form_field('alt_phone', 'Mobile / other phone', $v('alt_phone'), ['type' => 'tel']) ?>
            <?= form_field('email', 'Email', $v('email'), ['type' => 'email']) ?>
            <?= form_field('preferred_contact', 'Preferred contact method', $v('preferred_contact'), ['type' => 'select', 'options' => Client::CONTACT_METHODS]) ?>
            <?= form_field('website', 'Current website', $v('website'), ['placeholder' => 'example.com']) ?>
            <?= form_field('preferred_language', 'Preferred language', $v('preferred_language'), ['type' => 'select', 'options' => Client::LANGUAGES, 'hint' => 'Website briefs open in this language. Invoices and contracts are always in English.']) ?>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Address</h3><div class="card-subtitle ms-2">Used on invoices and contracts</div></div>
        <div class="card-body">
          <div class="row g-3">
            <?= form_field('address1', 'Address line 1', $v('address1')) ?>
            <?= form_field('address2', 'Address line 2', $v('address2'), ['placeholder' => 'Suite, unit…']) ?>
            <?= form_field('city', 'City', $v('city'), ['col' => 'col-md-5', 'placeholder' => 'McAllen']) ?>
            <?= form_field('state', 'State', $v('state') ?: 'TX', ['col' => 'col-md-3', 'type' => 'select', 'options' => Client::US_STATES]) ?>
            <?= form_field('zip', 'ZIP code', $v('zip'), ['col' => 'col-md-4']) ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Sales</h3></div>
        <div class="card-body">
          <div class="row g-3">
            <?= form_field('status', 'Status', $v('status'), ['col' => 'col-12', 'type' => 'select', 'options' => Client::STATUSES, 'hint' => 'Lead: not a customer yet. Active: buying or under contract.']) ?>
            <?= form_field('assigned_to', 'Assigned to', $assigned, ['col' => 'col-12', 'type' => 'select', 'options' => $userOptions, 'empty' => 'Unassigned']) ?>
            <?= form_field('source', 'Lead source', $v('source'), ['col' => 'col-12', 'type' => 'select', 'options' => $sources, 'empty' => '—']) ?>
            <?= form_switch('tax_exempt', 'Tax exempt', (bool)(int)$v('tax_exempt'), ['col' => 'col-12', 'hint' => 'Only with a valid Texas exemption certificate on file (e.g. non-profit, resale). No sales tax will be added to their invoices.']) ?>
          </div>
        </div>
      </div>
      <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Notes</h3></div>
        <div class="card-body">
          <div class="row g-3">
            <?= form_field('notes', 'Internal notes', $v('notes'), ['col' => 'col-12', 'type' => 'textarea', 'rows' => 5, 'hint' => 'Background about the client. For calls and follow-ups use the activity timeline.']) ?>
          </div>
        </div>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i><?= $isNew ? 'Create client' : 'Save changes' ?></button>
      </div>
    </div>
  </div>
</form>

<?php if (!$isNew && is_admin()): ?>
<div class="card border-danger-subtle mt-4">
  <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div>
      <div class="fw-bold">Delete this client</div>
      <div class="text-secondary small">Removes the client and their activity timeline. Website briefs are kept. This cannot be undone.</div>
    </div>
    <form method="post" action="<?= url('/clients/' . (int)$c['id'] . '/delete') ?>" data-confirm="Delete this client and their activity? This cannot be undone.">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-outline-danger"><i class="ti ti-trash me-1"></i>Delete client</button>
    </form>
  </div>
</div>
<?php endif; ?>
