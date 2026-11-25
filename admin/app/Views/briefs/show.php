<?php
use Crm\Core\Session;
use Crm\Core\View;
use Crm\Models\Brief;

$newLink = Session::peekFlash('new_brief_link');
?>
<?php View::start('actions'); ?>
<a href="<?= url('/briefs') ?>" class="btn"><i class="ti ti-arrow-left me-1"></i>All briefs</a>
<button type="button" class="btn btn-primary" data-copy="<?= e($link) ?>"><i class="ti ti-copy me-1"></i>Copy client link</button>
<?php View::end(); ?>

<?php if ($newLink): ?>
<div class="alert alert-success">
  <div class="fw-bold mb-1">Send this link to the client:</div>
  <code class="d-block text-break mb-2"><?= e($newLink) ?></code>
  <button type="button" class="btn btn-sm btn-success" data-copy="<?= e($newLink) ?>"><i class="ti ti-copy me-1"></i>Copy link</button>
</div>
<?php endif; ?>

<div class="card mb-3">
  <div class="card-body">
    <div class="d-flex flex-wrap gap-4">
      <div><div class="text-secondary small">Status</div><?= status_badge($brief['status'], Brief::STATUS_COLORS, Brief::STATUSES) ?></div>
      <div><div class="text-secondary small">Form language</div><?= $brief['lang'] === 'en' ? 'English' : 'Spanish' ?></div>
      <div><div class="text-secondary small">Created</div><?= e(fmt_datetime($brief['created_at'])) ?><?= $brief['created_by_name'] ? ' · ' . e($brief['created_by_name']) : '' ?></div>
      <div><div class="text-secondary small">Last update</div><?= e(fmt_datetime($brief['updated_at'])) ?></div>
      <?php if ($brief['completed_at']): ?><div><div class="text-secondary small">Submitted</div><?= e(fmt_datetime($brief['completed_at'])) ?></div><?php endif; ?>
    </div>
    <div class="mt-3 small text-secondary text-break"><i class="ti ti-link me-1"></i><?= e($link) ?></div>
  </div>
</div>

<?php if (!$data): ?>
  <div class="card"><div class="card-body text-secondary">The client has not filled in any information yet.</div></div>
<?php else: ?>
  <?php foreach ($schema as $step): ?>
  <div class="card mb-3">
    <div class="card-header"><h3 class="card-title"><?= e($step['title']) ?></h3></div>
    <div class="card-body crm-brief-view">
      <?php foreach ($step['fields'] as $fieldName => $field): ?>
        <?= ninubo_brief_render_view($fieldName, $field, $data[$fieldName] ?? null, 'en') ?>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php if (is_admin()): ?>
<div class="card border-danger-subtle">
  <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div>
      <div class="fw-bold">Delete this brief</div>
      <div class="text-secondary small">Removes the link and every answer. This cannot be undone.</div>
    </div>
    <form method="post" action="<?= url('/briefs/' . (int)$brief['id'] . '/delete') ?>" data-confirm="Delete this brief and all its answers? This cannot be undone.">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-outline-danger"><i class="ti ti-trash me-1"></i>Delete</button>
    </form>
  </div>
</div>
<?php endif; ?>
