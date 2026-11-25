<?php
use Crm\Core\Settings;
use Crm\Core\View;
use Crm\Models\Brief;
use Crm\Models\Package;

$taxTypes = Package::taxTypes();
$needsPrices = array_filter($packages, fn($p) => (int)$p['is_active'] && (float)$p['price'] <= 0);
?>
<?php if (is_admin()): ?>
<?php View::start('actions'); ?>
<a href="<?= url('/catalog/new') ?>" class="btn btn-primary"><i class="ti ti-plus me-1"></i>New package / service</a>
<?php View::end(); ?>
<?php endif; ?>

<?php if ($needsPrices && is_admin()): ?>
<div class="alert alert-warning"><i class="ti ti-alert-triangle me-1"></i><?= count($needsPrices) ?> active item(s) still have a $0.00 price. Edit them to set your prices.</div>
<?php endif; ?>

<div class="card">
  <?php if (!$packages): ?>
  <div class="card-body text-secondary">The catalog is empty.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-vcenter card-table">
      <thead>
        <tr><th>Package / service</th><th>Category</th><th class="text-end">Price</th><th>Sales tax</th><th>Website brief</th><?php if (is_admin()): ?><th class="w-1"></th><?php endif; ?></tr>
      </thead>
      <tbody>
      <?php foreach ($packages as $p): ?>
        <tr class="<?= (int)$p['is_active'] ? '' : 'text-secondary' ?>">
          <td>
            <div class="fw-medium"><?= e($p['name']) ?> <?php if (!(int)$p['is_active']): ?><span class="badge bg-secondary-lt">Inactive</span><?php endif; ?></div>
            <?php if ($p['description']): ?><div class="small text-secondary crm-clamp-2"><?= e($p['description']) ?></div><?php endif; ?>
          </td>
          <td><?= e(Package::CATEGORIES[$p['category']] ?? $p['category']) ?></td>
          <td class="text-end text-nowrap"><?= e(Package::priceLabel($p)) ?><div class="small text-secondary"><?= e(Package::BILLING[$p['billing']]) ?></div></td>
          <td class="small"><?= e($taxTypes[$p['tax_type']] ?? $p['tax_type']) ?></td>
          <td class="small"><?= $p['brief_profile'] ? e(Brief::profileLabel($p['brief_profile'])) : '<span class="text-secondary">—</span>' ?></td>
          <?php if (is_admin()): ?><td><a href="<?= url('/catalog/' . (int)$p['id'] . '/edit') ?>" class="btn btn-sm">Edit</a></td><?php endif; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
  <div class="card-footer small text-secondary">
    Sales tax rate: <?= e(Settings::get('sales_tax_rate')) ?>% (Business settings). Prices are in USD before tax.
  </div>
</div>
