<?php
use Crm\Core\View;
use Crm\Models\Activity;
use Crm\Models\Brief;
use Crm\Models\Client;

$id = (int)$client['id'];
$me = auth_user();
$tel = fn(string $n) => 'tel:' . preg_replace('/[^\d+]/', '', $n);
$wa = fn(string $n) => 'https://wa.me/' . (strlen($d = preg_replace('/\D/', '', $n)) === 10 ? '1' . $d : $d);
$openTasks = array_filter($activities, fn($a) => $a['type'] === 'task' && $a['done_at'] === null);
?>
<?php View::start('actions'); ?>
<a href="<?= url('/clients/' . $id . '/edit') ?>" class="btn"><i class="ti ti-pencil me-1"></i>Edit</a>
<div class="dropdown">
  <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"><i class="ti ti-plus me-1"></i>New</button>
  <div class="dropdown-menu dropdown-menu-end">
    <a class="dropdown-item" href="#new-brief" data-bs-toggle="modal"><i class="ti ti-forms dropdown-item-icon"></i>Website brief</a>
    <span class="dropdown-item disabled"><i class="ti ti-file-invoice dropdown-item-icon"></i>Invoice <span class="badge bg-secondary-lt ms-auto">soon</span></span>
    <span class="dropdown-item disabled"><i class="ti ti-file-certificate dropdown-item-icon"></i>Contract <span class="badge bg-secondary-lt ms-auto">soon</span></span>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item" href="#activity" data-focus-composer="task"><i class="ti ti-calendar-check dropdown-item-icon"></i>Follow-up</a>
    <a class="dropdown-item" href="#activity" data-focus-composer="note"><i class="ti ti-notes dropdown-item-icon"></i>Note</a>
  </div>
</div>
<?php View::end(); ?>

<div class="row row-cards">
  <!-- Left: client details -->
  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex align-items-center mb-3">
          <span class="avatar avatar-lg me-3 bg-primary-lt"><?= e(initials(Client::displayName($client))) ?></span>
          <div>
            <div class="fw-bold fs-3 lh-sm"><?= e(Client::displayName($client)) ?></div>
            <div class="mt-1"><?= status_badge($client['status'], Client::STATUS_COLORS, Client::STATUSES) ?>
              <?php if ((int)$client['tax_exempt']): ?><span class="badge bg-orange-lt">Tax exempt</span><?php endif; ?></div>
          </div>
        </div>

        <div class="mb-3">
          <div class="fw-medium"><?= e($client['contact_name']) ?></div>
          <?php if ($client['contact_title'] !== ''): ?><div class="text-secondary small"><?= e($client['contact_title']) ?></div><?php endif; ?>
        </div>

        <ul class="list-unstyled crm-contact-list mb-0">
          <?php if ($client['phone'] !== ''): ?>
          <li><i class="ti ti-phone"></i><a href="<?= e($tel($client['phone'])) ?>"><?= e($client['phone']) ?></a>
            <a href="<?= e($wa($client['phone'])) ?>" target="_blank" rel="noopener" class="ms-2 text-green" title="WhatsApp"><i class="ti ti-brand-whatsapp"></i></a></li>
          <?php endif; ?>
          <?php if ($client['alt_phone'] !== ''): ?>
          <li><i class="ti ti-device-mobile"></i><a href="<?= e($tel($client['alt_phone'])) ?>"><?= e($client['alt_phone']) ?></a></li>
          <?php endif; ?>
          <?php if ($client['email'] !== ''): ?>
          <li><i class="ti ti-mail"></i><a href="mailto:<?= e($client['email']) ?>"><?= e($client['email']) ?></a></li>
          <?php endif; ?>
          <?php if ($client['website'] !== ''): ?>
          <li><i class="ti ti-world"></i><a href="<?= e($client['website']) ?>" target="_blank" rel="noopener noreferrer"><?= e(preg_replace('#^https?://(www\.)?#', '', rtrim($client['website'], '/'))) ?></a></li>
          <?php endif; ?>
          <?php if ($lines = Client::addressLines($client)): ?>
          <li><i class="ti ti-map-pin"></i><span><?= implode('<br>', array_map('e', $lines)) ?></span></li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="card-body border-top">
        <dl class="row mb-0 small crm-dl">
          <dt class="col-5">Prefers</dt><dd class="col-7"><?= e(Client::CONTACT_METHODS[$client['preferred_contact']] ?? '—') ?></dd>
          <dt class="col-5">Language</dt><dd class="col-7"><?= e(Client::LANGUAGES[$client['preferred_language']] ?? '—') ?></dd>
          <dt class="col-5">Industry</dt><dd class="col-7"><?= e($client['industry'] ?: '—') ?></dd>
          <dt class="col-5">Lead source</dt><dd class="col-7"><?= e($client['source'] ?: '—') ?></dd>
          <dt class="col-5">Assigned to</dt><dd class="col-7"><?= e($client['assigned_name'] ?: 'Unassigned') ?></dd>
          <dt class="col-5">Created</dt><dd class="col-7"><?= e(fmt_date($client['created_at'])) ?><?= $client['created_by_name'] ? ' · ' . e($client['created_by_name']) : '' ?></dd>
        </dl>
      </div>
    </div>

    <?php if (trim((string)$client['notes']) !== ''): ?>
    <div class="card mb-3">
      <div class="card-header"><h3 class="card-title">Notes</h3></div>
      <div class="card-body"><?= nl2br(e($client['notes'])) ?></div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Right: everything created from this client -->
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title"><i class="ti ti-forms me-2"></i>Website briefs</h3>
        <div class="card-actions"><a href="#new-brief" data-bs-toggle="modal" class="btn btn-sm"><i class="ti ti-plus me-1"></i>New brief</a></div>
      </div>
      <?php if (!$briefs): ?>
      <div class="card-body text-secondary">No briefs yet. Send one so the client can fill in the information for their website.</div>
      <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($briefs as $b): ?>
        <div class="list-group-item">
          <div class="row align-items-center g-2">
            <div class="col">
              <a href="<?= url('/briefs/' . (int)$b['id']) ?>" class="fw-medium"><?= e($b['package_name'] ?: 'Website brief') ?></a>
              <div class="small text-secondary"><?= e(Brief::profileLabel($b['brief_profile'])) ?> · created <?= e(fmt_date($b['created_at'])) ?> · updated <?= e(time_ago($b['updated_at'])) ?></div>
            </div>
            <div class="col-auto"><?= status_badge($b['status'], Brief::STATUS_COLORS, Brief::STATUSES) ?></div>
            <div class="col-auto"><button type="button" class="btn btn-sm" data-copy="<?= e(Brief::publicLink($b)) ?>"><i class="ti ti-copy me-1"></i>Copy link</button></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="card mb-3">
      <div class="card-header"><h3 class="card-title"><i class="ti ti-file-invoice me-2"></i>Invoices &amp; contracts</h3></div>
      <div class="card-body text-secondary small">Invoices and contracts for this client will appear here (next phases).</div>
    </div>

    <div class="card" id="activity">
      <div class="card-header">
        <h3 class="card-title"><i class="ti ti-timeline me-2"></i>Activity</h3>
        <?php if ($openTasks): ?><span class="badge bg-orange-lt ms-2"><?= count($openTasks) ?> open follow-up<?= count($openTasks) === 1 ? '' : 's' ?></span><?php endif; ?>
      </div>
      <div class="card-body border-bottom">
        <form method="post" action="<?= url('/clients/' . $id . '/activities') ?>" class="crm-composer">
          <?= csrf_field() ?>
          <div class="crm-type-picker mb-2" role="radiogroup" aria-label="Activity type">
            <?php foreach (Activity::TYPES as $key => [$label, $icon]): if ($key === 'system') continue; ?>
            <input type="radio" class="btn-check" name="type" id="type_<?= $key ?>" value="<?= $key ?>" autocomplete="off"<?= old('type', 'note') === $key ? ' checked' : '' ?>>
            <label for="type_<?= $key ?>" class="btn btn-sm"><i class="ti ti-<?= $icon ?> me-1"></i><?= e($label) ?></label>
            <?php endforeach; ?>
          </div>
          <textarea class="form-control mb-2" name="body" rows="2" placeholder="What happened, or what needs to happen next?" required><?= e(old('body')) ?></textarea>
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="crm-due" data-due-field>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                <input type="datetime-local" class="form-control" name="due_at" value="<?= e(old('due_at')) ?>" aria-label="Due date">
              </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm ms-auto">Save</button>
          </div>
        </form>
      </div>

      <?php if (!$activities): ?>
      <div class="card-body text-secondary">No activity yet.</div>
      <?php else: ?>
      <div class="list-group list-group-flush crm-timeline">
        <?php foreach ($activities as $a):
          [$label, $icon, $color] = Activity::TYPES[$a['type']] ?? Activity::TYPES['note'];
          $isTask = $a['type'] === 'task';
          $overdue = $isTask && $a['done_at'] === null && $a['due_at'] !== null && strtotime($a['due_at']) < time();
          $canDelete = $a['type'] !== 'system' && ((int)$a['user_id'] === (int)$me['id'] || $me['role'] === 'admin');
        ?>
        <div class="list-group-item<?= $a['type'] === 'system' ? ' crm-timeline-system' : '' ?>">
          <div class="d-flex gap-3">
            <span class="avatar avatar-sm bg-<?= $color ?>-lt flex-shrink-0"><i class="ti ti-<?= $icon ?>"></i></span>
            <div class="flex-fill">
              <div class="d-flex flex-wrap align-items-center gap-2">
                <strong class="small"><?= e($label) ?></strong>
                <?php if ($isTask): ?>
                  <?php if ($a['done_at']): ?><span class="badge bg-green-lt">Done</span>
                  <?php elseif ($overdue): ?><span class="badge bg-red-lt">Overdue</span><?php endif; ?>
                  <?php if ($a['due_at']): ?><span class="small text-secondary"><i class="ti ti-calendar"></i> Due <?= e(fmt_datetime($a['due_at'])) ?></span><?php endif; ?>
                <?php endif; ?>
                <span class="small text-secondary ms-auto" title="<?= e(fmt_datetime($a['created_at'])) ?>"><?= e($a['user_name'] ?: 'System') ?> · <?= e(time_ago($a['created_at'])) ?></span>
              </div>
              <div class="mt-1<?= $isTask && $a['done_at'] ? ' text-decoration-line-through text-secondary' : '' ?>"><?= nl2br(e($a['body'])) ?></div>
              <?php if ($isTask || $canDelete): ?>
              <div class="mt-2 d-flex gap-2">
                <?php if ($isTask): ?>
                <form method="post" action="<?= url('/activities/' . (int)$a['id'] . '/toggle') ?>" class="m-0">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-ghost-<?= $a['done_at'] ? 'secondary' : 'success' ?> px-2"><i class="ti ti-<?= $a['done_at'] ? 'arrow-back-up' : 'check' ?> me-1"></i><?= $a['done_at'] ? 'Reopen' : 'Mark done' ?></button>
                </form>
                <?php endif; ?>
                <?php if ($canDelete): ?>
                <form method="post" action="<?= url('/activities/' . (int)$a['id'] . '/delete') ?>" class="m-0" data-confirm="Delete this activity?">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-ghost-danger px-2"><i class="ti ti-trash"></i></button>
                </form>
                <?php endif; ?>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?= View::partial('briefs/_new_modal', [
    'clientId' => $id,
    'clientLanguage' => $client['preferred_language'],
    'briefPackages' => $briefPackages,
    'clientOptions' => null,
]) ?>
