<?php
use Crm\Core\Session;

$icons = ['success' => 'circle-check', 'danger' => 'alert-circle', 'warning' => 'alert-triangle', 'info' => 'info-circle'];
foreach ($icons as $type => $icon):
    $message = Session::peekFlash($type);
    if (!$message) {
        continue;
    }
?>
<div class="alert alert-<?= $type ?> alert-dismissible" role="alert">
  <div class="d-flex align-items-center gap-2">
    <i class="ti ti-<?= $icon ?>"></i>
    <div><?= e($message) ?></div>
  </div>
  <a class="btn-close" data-bs-dismiss="alert" aria-label="Close"></a>
</div>
<?php endforeach; ?>
