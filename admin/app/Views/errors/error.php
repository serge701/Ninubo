<div class="empty">
  <div class="empty-header"><?= (int)$code ?></div>
  <p class="empty-title"><?= e($title) ?></p>
  <p class="empty-subtitle text-secondary"><?= e($message) ?></p>
  <?php if (!empty($detail)): ?>
  <pre class="text-start small text-danger" style="white-space:pre-wrap;max-width:720px"><?= e($detail) ?></pre>
  <?php endif; ?>
  <div class="empty-action">
    <a href="<?= url('/') ?>" class="btn btn-primary"><i class="ti ti-arrow-left me-1"></i>Back to the dashboard</a>
  </div>
</div>
