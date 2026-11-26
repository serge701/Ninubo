<?php
use Crm\Core\View;
use Crm\Models\Package;

$isNew = $package === null;
$p = $package ?? ['name' => '', 'category' => 'website', 'description' => '', 'price' => '0.00', 'price_type' => 'fixed',
    'price_max' => null, 'internal_cost' => null, 'billing' => 'one_time', 'tax_type' => 'data_processing',
    'brief_profile' => null, 'is_active' => 1, 'sort_order' => 0];
$v = fn(string $k) => old($k, $p[$k] ?? '');

$profiles = ['' => 'No brief (not a website build)'];
foreach (ninubo_brief_profiles() as $key => $label) {
    $profiles[$key] = $label . ' — ' . count(ninubo_brief_schema('en', $key)) . ' steps';
}
?>
<?php View::start('actions'); ?>
<a href="<?= url('/catalog') ?>" class="btn"><i class="ti ti-arrow-left me-1"></i>Catalog</a>
<?php View::end(); ?>

<div class="row row-cards">
  <div class="col-lg-8">
    <form class="card" method="post" action="<?= url($isNew ? '/catalog' : '/catalog/' . (int)$p['id']) ?>" novalidate>
      <?= csrf_field() ?>
      <div class="card-body">
        <div class="row g-3">
          <?= form_field('name', 'Name', $v('name'), ['required' => true, 'col' => 'col-md-8', 'placeholder' => 'e.g. Business Website']) ?>
          <?= form_field('category', 'Category', $v('category'), ['type' => 'select', 'col' => 'col-md-4', 'options' => Package::CATEGORIES]) ?>
          <?= form_field('description', 'Description', $v('description'), ['type' => 'textarea', 'col' => 'col-12', 'rows' => 4, 'hint' => 'What is included. Printed on invoices and used as the scope of work in contracts — write it for the client.']) ?>
          <?= form_field('price_type', 'Pricing', $v('price_type'), ['type' => 'select', 'col' => 'col-md-4', 'options' => Package::PRICE_TYPES]) ?>
          <?= form_field('price', 'Price (or starting price)', $v('price'), ['type' => 'number', 'step' => '0.01', 'col' => 'col-md-4', 'prefix' => '$', 'required' => true]) ?>
          <?= form_field('price_max', 'Maximum price', (string)$v('price_max'), ['type' => 'number', 'step' => '0.01', 'col' => 'col-md-4', 'prefix' => '$', 'hint' => 'Only for price ranges, e.g. $200 – $400.']) ?>
          <?= form_field('billing', 'Billing', $v('billing'), ['type' => 'select', 'col' => 'col-md-4', 'options' => Package::BILLING]) ?>
          <?= form_field('internal_cost', 'Production cost (internal)', (string)$v('internal_cost'), ['type' => 'number', 'step' => '0.01', 'col' => 'col-md-4', 'prefix' => '$', 'hint' => 'Partner price paid for production. Admin-only; used to show the margin.']) ?>
          <?= form_field('sort_order', 'Sort order', $v('sort_order'), ['type' => 'number', 'col' => 'col-md-4', 'hint' => 'Lower numbers show first.']) ?>
          <?= form_field('tax_type', 'Texas sales tax', $v('tax_type'), ['type' => 'select', 'col' => 'col-md-6', 'options' => Package::taxTypes(), 'hint' => 'Web design, hosting and maintenance are data processing services. Training is usually not taxable. Confirm with your CPA.']) ?>
          <?= form_field('brief_profile', 'Website brief', (string)$v('brief_profile'), ['type' => 'select', 'col' => 'col-md-6', 'options' => $profiles, 'hint' => 'Which questions the client answers when you send them a brief for this package.']) ?>
          <?php if (!$isNew): ?>
          <?= form_switch('is_active', 'Active (available for new sales)', (bool)(int)$v('is_active'), ['col' => 'col-12']) ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary"><?= $isNew ? 'Add to catalog' : 'Save changes' ?></button>
      </div>
    </form>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Brief profiles</h3></div>
      <div class="card-body small">
        <p><strong>Starter — landing page</strong> — business info, services, up to 5 page sections, visuals, Google &amp; reviews, contact, domain and design basics.</p>
        <p><strong>Business website</strong> — everything: ideal customer, pages (up to 7), about us, Google &amp; SEO, competitors, features and legal.</p>
        <p><strong>Premium website</strong> — the business questions plus blog, advanced forms, integrations, local SEO cities, Google Business Profile access and animations.</p>
        <p class="mb-0"><strong>Online store</strong> — the business questions plus products, payments, shipping, inventory and returns.</p>
      </div>
    </div>
    <?php if (!$isNew): ?>
    <div class="card mt-3">
      <div class="card-body">
        <form method="post" action="<?= url('/catalog/' . (int)$p['id'] . '/delete') ?>" data-confirm="&quot;<?= e($p['name']) ?>&quot; will be removed from the catalog." data-confirm-title="Delete this item?" data-confirm-button="Delete">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-outline-danger w-100"><i class="ti ti-trash me-1"></i>Delete</button>
        </form>
        <div class="form-hint">Only possible if it was never used. Otherwise mark it inactive.</div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
