<?php
use Crm\Models\Brief;

$me = auth_user();
?>
<div class="row row-deck row-cards mb-3">
  <?php
  $cards = [
      ['en_progreso', 'In progress', 'yellow', 'pencil'],
      ['pendiente', 'Not started', 'secondary', 'clock'],
      ['completado', 'Completed', 'green', 'circle-check'],
  ];
  foreach ($cards as [$key, $label, $color, $icon]): ?>
  <div class="col-sm-6 col-lg-3">
    <a class="card card-sm card-link" href="<?= url('/briefs?status=' . $key) ?>">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto"><span class="bg-<?= $color ?> text-white avatar"><i class="ti ti-<?= $icon ?>"></i></span></div>
          <div class="col">
            <div class="font-weight-medium"><?= (int)$briefCounts[$key] ?> brief<?= (int)$briefCounts[$key] === 1 ? '' : 's' ?></div>
            <div class="text-secondary"><?= e($label) ?></div>
          </div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
  <div class="col-sm-6 col-lg-3">
    <div class="card card-sm">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto"><span class="bg-primary text-white avatar"><i class="ti ti-users"></i></span></div>
          <div class="col">
            <div class="font-weight-medium"><?= (int)$userCount ?> active user<?= $userCount === 1 ? '' : 's' ?></div>
            <div class="text-secondary">Team</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row row-cards">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Recent website briefs</h3>
        <div class="card-actions"><a href="<?= url('/briefs') ?>" class="btn btn-sm">View all</a></div>
      </div>
      <?php if (!$recentBriefs): ?>
      <div class="card-body text-secondary">No briefs yet. Create the first link from <a href="<?= url('/briefs') ?>">Website briefs</a>.</div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-vcenter card-table">
          <thead><tr><th>Client</th><th>Status</th><th>Last update</th></tr></thead>
          <tbody>
          <?php foreach ($recentBriefs as $b): ?>
            <tr>
              <td><a href="<?= url('/briefs/' . (int)$b['id']) ?>"><?= e($b['client_label']) ?></a></td>
              <td><?= status_badge($b['status'], Brief::STATUS_COLORS, Brief::STATUSES) ?></td>
              <td class="text-secondary"><?= e(fmt_datetime($b['updated_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Welcome, <?= e(explode(' ', $me['name'])[0]) ?></h3></div>
      <div class="card-body">
        <?php if (is_admin()): ?>
        <p class="text-secondary">Getting the CRM ready:</p>
        <ul class="list-unstyled space-y-2 mb-0">
          <li>
            <i class="ti ti-<?= $businessConfigured ? 'circle-check text-green' : 'circle text-secondary' ?> me-1"></i>
            <a href="<?= url('/settings') ?>">Fill in your business details</a> (shown on invoices and contracts)
          </li>
          <li>
            <i class="ti ti-<?= $userCount > 1 ? 'circle-check text-green' : 'circle text-secondary' ?> me-1"></i>
            <a href="<?= url('/users/new') ?>">Add your sales team</a>
          </li>
        </ul>
        <?php else: ?>
        <p class="text-secondary mb-0">Use the menu on the left to work with website briefs.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
