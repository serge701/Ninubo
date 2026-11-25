<?php
use Crm\Core\View;
use Crm\Models\User;
?>
<?php View::start('actions'); ?>
<a href="<?= url('/users/new') ?>" class="btn btn-primary"><i class="ti ti-user-plus me-1"></i>New user</a>
<?php View::end(); ?>

<div class="card">
  <div class="table-responsive">
    <table class="table table-vcenter card-table">
      <thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Last sign-in</th><th class="w-1"></th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr class="<?= (int)$u['is_active'] ? '' : 'text-secondary' ?>">
          <td>
            <div class="d-flex align-items-center">
              <span class="avatar avatar-sm me-2"><?= e(initials($u['name'])) ?></span>
              <div>
                <div class="fw-medium"><?= e($u['name']) ?></div>
                <?php if ($u['email']): ?><div class="small text-secondary"><?= e($u['email']) ?></div><?php endif; ?>
              </div>
            </div>
          </td>
          <td><?= e($u['username']) ?></td>
          <td><?= status_badge($u['role'], ['admin' => 'purple', 'sales' => 'blue'], User::ROLES) ?></td>
          <td>
            <?= (int)$u['is_active'] ? '<span class="badge bg-green-lt">Active</span>' : '<span class="badge bg-secondary-lt">Disabled</span>' ?>
            <?php if ((int)$u['must_change_password']): ?><span class="badge bg-yellow-lt" title="Must change password at next sign-in">Temp. password</span><?php endif; ?>
          </td>
          <td class="text-secondary"><?= e(fmt_datetime($u['last_login_at'])) ?></td>
          <td><a href="<?= url('/users/' . (int)$u['id'] . '/edit') ?>" class="btn btn-sm">Edit</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
