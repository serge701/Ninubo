<?php
use Crm\Core\Settings;
use Crm\Core\View;
use Crm\Models\Brief;
use Crm\Models\Package;

$taxTypes = Package::taxTypes();
$admin = is_admin();
$needsPrices = array_filter($packages, fn($p) => (int)$p['is_active'] && (float)$p['price'] <= 0);

// Group by category, in the order categories are declared.
$groups = array_fill_keys(array_keys(Package::CATEGORIES), []);
foreach ($packages as $p) {
    $groups[$p['category']][] = $p;
}
$groups = array_filter($groups);
?>
<?php if ($admin): ?>
<?php View::start('actions'); ?>
<a href="<?= url('/catalog/new') ?>" class="btn btn-primary"><i class="ti ti-plus me-1"></i>New package / service</a>
<?php View::end(); ?>
<?php endif; ?>

<?php if ($needsPrices && $admin): ?>
<div class="alert alert-warning"><i class="ti ti-alert-triangle me-1"></i><?= count($needsPrices) ?> active item(s) still have a $0 price. Edit them to set your prices.</div>
<?php endif; ?>

<?php if (!$packages): ?>
<div class="card"><div class="card-body text-secondary">The catalog is empty.</div></div>
<?php endif; ?>

<?php foreach ($groups as $category => $items): ?>
<div class="card mb-3">
  <div class="card-header"><h3 class="card-title"><?= e(Package::CATEGORIES[$category] ?? $category) ?></h3></div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table">
      <thead>
        <tr>
          <th>Package / service</th>
          <th class="text-end">Price</th>
          <?php if ($admin): ?><th class="text-end">Production cost<span class="d-block fw-normal text-secondary">internal · margin</span></th><?php endif; ?>
          <th>Sales tax</th>
          <th>Website brief</th>
          <?php if ($admin): ?><th class="w-1"></th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($items as $p): $margin = Package::margin($p); ?>
        <tr class="<?= (int)$p['is_active'] ? '' : 'text-secondary' ?>">
          <td>
            <div class="fw-medium"><?= e($p['name']) ?> <?php if (!(int)$p['is_active']): ?><span class="badge bg-secondary-lt">Inactive</span><?php endif; ?></div>
            <?php if ($p['description']): ?><div class="small text-secondary crm-clamp-2" title="<?= e($p['description']) ?>"><?= e($p['description']) ?></div><?php endif; ?>
          </td>
          <td class="text-end text-nowrap fw-medium"><?= e(Package::priceLabel($p)) ?><div class="small text-secondary fw-normal"><?= e(Package::BILLING[$p['billing']]) ?></div></td>
          <?php if ($admin): ?>
          <td class="text-end text-nowrap small">
            <?php if ($p['internal_cost'] !== null): ?>
              <?= e(money($p['internal_cost'])) ?>
              <?php if ($margin): ?><div class="text-green"><?= e(money($margin['amount'])) ?> (<?= number_format($margin['pct'], 1) ?>%)</div><?php endif; ?>
            <?php else: ?><span class="text-secondary">—</span><?php endif; ?>
          </td>
          <?php endif; ?>
          <td class="small"><?= e($taxTypes[$p['tax_type']] ?? $p['tax_type']) ?></td>
          <td class="small"><?= $p['brief_profile'] ? e(Brief::profileLabel($p['brief_profile'])) : '<span class="text-secondary">—</span>' ?></td>
          <?php if ($admin): ?><td><a href="<?= url('/catalog/' . (int)$p['id'] . '/edit') ?>" class="btn btn-sm">Edit</a></td><?php endif; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endforeach; ?>

<p class="small text-secondary">
  Prices in USD before sales tax (rate <?= e(Settings::get('sales_tax_rate')) ?>%, Business settings).
  "From" and range prices are suggestions — the final amount is set on each invoice.
  <?php if ($admin): ?>Production cost and margin are visible to administrators only.<?php endif; ?>
</p>
