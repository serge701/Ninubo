<?php
use Crm\Core\View;
use Crm\Models\Brief;
use Crm\Models\Client;
use Crm\Models\Invoice;

$id = (int)$inv['id'];
$status = Invoice::displayStatus($inv);
$balance = Invoice::balance($inv);
$isDraft = $inv['status'] === 'draft';
$canPay = in_array($inv['status'], ['sent', 'partial'], true);
?>
<?php View::start('actions'); ?>
<a href="<?= url('/clients/' . (int)$inv['client_id']) ?>" class="btn"><i class="ti ti-address-book me-1"></i>Client</a>
<?php if ($isDraft): ?>
  <a href="<?= url('/invoices/' . $id . '/edit') ?>" class="btn"><i class="ti ti-pencil me-1"></i>Edit</a>
  <form method="post" action="<?= url('/invoices/' . $id . '/issue') ?>" class="m-0" data-confirm="It gets its invoice number and can no longer be edited. Then you can download the PDF and record payments." data-confirm-title="Issue this invoice?" data-confirm-button="Issue invoice" data-confirm-variant="primary">
    <?= csrf_field() ?><button class="btn btn-primary"><i class="ti ti-send me-1"></i>Issue invoice</button>
  </form>
<?php else: ?>
  <a href="<?= url('/invoices/' . $id . '/pdf') ?>" class="btn btn-primary"><i class="ti ti-download me-1"></i>Download PDF</a>
  <?php if ($canPay): ?><a href="#record-payment" data-bs-toggle="modal" class="btn btn-success"><i class="ti ti-cash me-1"></i>Record payment</a><?php endif; ?>
<?php endif; ?>
<?php View::end(); ?>

<?php if ($isDraft): ?>
<div class="alert alert-info"><i class="ti ti-info-circle me-1"></i>This is a <strong>draft</strong>: it has no number yet and the client hasn't seen it. Review the preview and click <strong>Issue invoice</strong>.</div>
<?php elseif ($inv['status'] === 'void'): ?>
<div class="alert alert-secondary"><i class="ti ti-ban me-1"></i>Voided on <?= e(fmt_datetime($inv['voided_at'])) ?>: <?= e($inv['void_reason']) ?></div>
<?php endif; ?>

<div class="row row-cards">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body p-0">
        <iframe src="<?= url('/invoices/' . $id . '/pdf') ?>?inline=1#toolbar=0&navpanes=0&view=FitH" title="Invoice preview" class="crm-pdf-frame"></iframe>
      </div>
      <div class="card-footer small text-secondary d-flex justify-content-between">
        <span>Preview of the PDF the client receives.</span>
        <a href="<?= url('/invoices/' . $id . '/pdf') ?>?inline=1" target="_blank" rel="noopener">Open in a new tab</a>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-secondary small">Status</div>
            <?= status_badge($status, Invoice::STATUS_COLORS, Invoice::STATUSES) ?>
          </div>
          <div class="text-end">
            <div class="text-secondary small">Balance due</div>
            <div class="fs-2 fw-bold<?= $balance > 0 && $status === 'overdue' ? ' text-red' : '' ?>"><?= e(money($inv['status'] === 'void' ? 0 : $balance)) ?></div>
          </div>
        </div>
        <hr class="my-3">
        <dl class="row mb-0 small crm-dl">
          <dt class="col-5">Client</dt><dd class="col-7"><a href="<?= url('/clients/' . (int)$inv['client_id']) ?>"><?= e(Client::displayName($inv)) ?></a></dd>
          <dt class="col-5">Total</dt><dd class="col-7"><?= e(money($inv['total'])) ?></dd>
          <dt class="col-5">Paid</dt><dd class="col-7"><?= e(money($inv['amount_paid'])) ?></dd>
          <dt class="col-5">Issue date</dt><dd class="col-7"><?= e(fmt_date($inv['issue_date'])) ?></dd>
          <dt class="col-5">Due date</dt><dd class="col-7"><?= e(fmt_date($inv['due_date'])) ?></dd>
          <dt class="col-5">Created by</dt><dd class="col-7"><?= e($inv['created_by_name'] ?: '—') ?></dd>
        </dl>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header"><h3 class="card-title">Payment schedule</h3><span class="card-subtitle ms-2"><?= e(Invoice::TERMS[$inv['payment_terms']][0] ?? '') ?></span></div>
      <div class="list-group list-group-flush">
        <?php foreach ($schedule as $step): ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between">
            <span class="fw-medium"><?= e($step['label']) ?> (<?= (int)$step['pct'] ?>%)</span>
            <span><?= e(money($step['amount'])) ?></span>
          </div>
          <div class="d-flex justify-content-between small text-secondary">
            <span><?= e($step['when']) ?></span>
            <span><?php if ($step['status'] === 'paid'): ?><span class="text-green"><i class="ti ti-check"></i> Paid</span>
              <?php elseif ($step['status'] === 'partial'): ?><span class="text-yellow"><?= e(money($step['paid'])) ?> paid</span>
              <?php else: ?>Pending<?php endif; ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (!$isDraft): ?>
    <div class="card mb-3">
      <div class="card-header"><h3 class="card-title">Payments</h3></div>
      <?php if (!$payments): ?>
      <div class="card-body text-secondary small">No payments recorded yet.</div>
      <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($payments as $p): ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-medium"><?= e(money($p['amount'])) ?> <span class="text-secondary fw-normal small">· <?= e(Invoice::PAYMENT_METHODS[$p['method']] ?? $p['method']) ?></span></div>
              <div class="small text-secondary"><?= e(fmt_date($p['paid_on'])) ?><?= $p['reference'] !== '' ? ' · Ref ' . e($p['reference']) : '' ?><?= $p['recorded_by_name'] ? ' · ' . e($p['recorded_by_name']) : '' ?></div>
              <?php if ($p['notes'] !== ''): ?><div class="small"><?= e($p['notes']) ?></div><?php endif; ?>
            </div>
            <?php if (is_admin() && $inv['status'] !== 'void'): ?>
            <form method="post" action="<?= url('/payments/' . (int)$p['id'] . '/delete') ?>" class="m-0" data-confirm="The payment of <?= e(money($p['amount'])) ?> is removed and the balance is recalculated." data-confirm-title="Delete this payment?" data-confirm-button="Delete payment">
              <?= csrf_field() ?><button class="btn btn-sm btn-ghost-danger px-2" title="Delete payment"><i class="ti ti-trash"></i></button>
            </form>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($briefs): ?>
    <div class="card mb-3">
      <div class="card-header"><h3 class="card-title"><i class="ti ti-forms me-2"></i>Website brief</h3></div>
      <div class="list-group list-group-flush">
        <?php foreach ($briefs as $b): ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between align-items-center gap-2">
            <a href="<?= url('/briefs/' . (int)$b['id']) ?>" class="fw-medium"><?= e($b['package_name'] ?: 'Website brief') ?></a>
            <?= status_badge($b['status'], Brief::STATUS_COLORS, Brief::STATUSES) ?>
          </div>
          <button type="button" class="btn btn-sm mt-2" data-copy="<?= e(Brief::publicLink($b)) ?>"><i class="ti ti-copy me-1"></i>Copy link</button>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="card-footer small text-secondary">Created when the invoice was issued. The link is also printed on the PDF.</div>
    </div>
    <?php endif; ?>

    <?php if ($inv['internal_notes']): ?>
    <div class="card mb-3">
      <div class="card-header"><h3 class="card-title">Internal notes</h3></div>
      <div class="card-body small"><?= nl2br(e($inv['internal_notes'])) ?></div>
    </div>
    <?php endif; ?>

    <?php if ($isDraft): ?>
    <form method="post" action="<?= url('/invoices/' . $id . '/delete') ?>" data-confirm="The draft and its lines are removed." data-confirm-title="Delete this draft?" data-confirm-button="Delete draft" class="text-end">
      <?= csrf_field() ?><button class="btn btn-link link-danger btn-sm p-0"><i class="ti ti-trash me-1"></i>Delete draft</button>
    </form>
    <?php elseif (is_admin() && $inv['status'] !== 'void'): ?>
    <div class="card border-danger-subtle">
      <div class="card-body">
        <form method="post" action="<?= url('/invoices/' . $id . '/void') ?>" data-confirm="Invoice <?= e($inv['number']) ?> is voided and its number is never reused. Recurring services it started are cancelled. This cannot be undone." data-confirm-title="Void this invoice?" data-confirm-button="Void invoice">
          <?= csrf_field() ?>
          <label class="form-label" for="void_reason">Void this invoice</label>
          <input type="text" class="form-control form-control-sm mb-2" id="void_reason" name="void_reason" placeholder="Reason (e.g. wrong package, replaced by INV-…)" required>
          <button class="btn btn-outline-danger btn-sm w-100"><i class="ti ti-ban me-1"></i>Void invoice</button>
        </form>
        <div class="form-hint">The number is kept and never reused. Recurring services started by this invoice are cancelled, and periods it billed are reopened.</div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($canPay): ?>
<div class="modal modal-blur fade" id="record-payment" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form class="modal-content" method="post" action="<?= url('/invoices/' . $id . '/payments') ?>">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title">Record payment — <?= e($inv['number']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php $next = array_values(array_filter($schedule, fn($s) => $s['status'] !== 'paid'))[0] ?? null; ?>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label required" for="pay_amount">Amount received</label>
            <div class="input-group"><span class="input-group-text">$</span>
              <input type="number" step="0.01" min="0.01" max="<?= e(number_format($balance, 2, '.', '')) ?>" class="form-control" id="pay_amount" name="amount" value="<?= e(number_format($next ? $next['amount'] - $next['paid'] : $balance, 2, '.', '')) ?>" required></div>
            <div class="form-hint"><?= $next ? e($next['label']) . ' pending · ' : '' ?>Balance <?= e(money($balance)) ?></div>
          </div>
          <div class="col-md-6">
            <label class="form-label required" for="pay_date">Date received</label>
            <input type="date" class="form-control" id="pay_date" name="paid_on" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="pay_method">Method</label>
            <select class="form-select" id="pay_method" name="method">
              <?php foreach (Invoice::PAYMENT_METHODS as $k => $label): ?><option value="<?= e($k) ?>"><?= e($label) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="pay_ref">Reference</label>
            <input type="text" class="form-control" id="pay_ref" name="reference" maxlength="120" placeholder="Check #, Zelle confirmation…">
          </div>
          <div class="col-12">
            <label class="form-label" for="pay_notes">Notes</label>
            <input type="text" class="form-control" id="pay_notes" name="notes" maxlength="255">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Save payment</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
