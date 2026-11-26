<?php
use Crm\Core\Session;
use Crm\Core\View;
use Crm\Models\Client;
use Crm\Models\Contract;

$id = (int)$k['id'];
$st = $k['status'];
$newLink = Session::peekFlash('new_sign_link');
$mailto = null;
if ($link && $k['client_signer_email'] !== '') {
    $subject = 'Please review and sign: ' . $k['title'] . ' ' . $k['number'];
    $bodyText = "Hello {$k['client_signer_name']},\n\nPlease review and sign the agreement for your website here:\n{$link}\n\n"
        . "You can read the full agreement, sign it online and download a copy.\n\nThank you,\n" . $s['business_name'];
    $mailto = 'mailto:' . rawurlencode($k['client_signer_email']) . '?subject=' . rawurlencode($subject) . '&body=' . rawurlencode($bodyText);
}
?>
<?php View::start('actions'); ?>
<a href="<?= url('/clients/' . (int)$k['client_id']) ?>" class="btn"><i class="ti ti-address-book me-1"></i>Client</a>
<?php if ($st === 'draft'): ?>
  <a href="<?= url('/contracts/' . $id . '/edit') ?>" class="btn"><i class="ti ti-pencil me-1"></i>Edit</a>
  <form method="post" action="<?= url('/contracts/' . $id . '/finalize') ?>" class="m-0"
        data-confirm="The agreement gets its number, the text is locked and a private signing link is created for the client."
        data-confirm-title="Finalize this contract?" data-confirm-button="Finalize" data-confirm-variant="primary">
    <?= csrf_field() ?><button class="btn btn-primary"><i class="ti ti-lock me-1"></i>Finalize &amp; get signing link</button>
  </form>
<?php else: ?>
  <a href="<?= url('/contracts/' . $id . '/pdf') ?>" class="btn btn-primary"><i class="ti ti-download me-1"></i><?= $st === 'signed' ? 'Download signed PDF' : 'Download PDF' ?></a>
  <?php if ($st === 'client_signed' && is_admin()): ?>
  <a href="#countersign" data-bs-toggle="modal" class="btn btn-success"><i class="ti ti-signature me-1"></i>Countersign</a>
  <?php endif; ?>
<?php endif; ?>
<?php View::end(); ?>

<?php if (is_admin() && $st === 'draft'): ?>
<div class="alert alert-info small"><i class="ti ti-scale me-1"></i>This agreement template was written for Ninubo from your commercial terms. Have it reviewed once by a Texas attorney before using it with clients.</div>
<?php endif; ?>
<?php if ($st === 'cancelled'): ?>
<div class="alert alert-secondary"><i class="ti ti-ban me-1"></i>Cancelled on <?= e(fmt_datetime($k['cancelled_at'])) ?>: <?= e($k['cancel_reason']) ?>. The signing link no longer works.</div>
<?php endif; ?>

<?php if ($newLink || ($st === 'sent' && $link)): ?>
<div class="alert alert-success">
  <div class="fw-bold mb-1"><i class="ti ti-link me-1"></i>Signing link for <?= e($k['client_signer_name']) ?></div>
  <code class="d-block text-break mb-2"><?= e($link) ?></code>
  <div class="d-flex flex-wrap gap-2">
    <button type="button" class="btn btn-sm btn-success" data-copy="<?= e($link) ?>"><i class="ti ti-copy me-1"></i>Copy link</button>
    <?php if ($mailto): ?><a href="<?= e($mailto) ?>" class="btn btn-sm"><i class="ti ti-mail me-1"></i>Email it to <?= e($k['client_signer_email']) ?></a><?php endif; ?>
  </div>
  <div class="small mt-2">The client reads the agreement, agrees to sign electronically, signs and can download a copy. You can also send it by WhatsApp.</div>
</div>
<?php endif; ?>

<div class="row row-cards">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><?= $st === 'draft' ? 'Preview' : 'Agreement text' ?></h3>
        <?php if ($st !== 'draft' && $k['body_hash']): ?><span class="card-subtitle ms-2 text-truncate" title="SHA-256 fingerprint of the locked text">SHA-256 <?= e(substr($k['body_hash'], 0, 16)) ?>…</span><?php endif; ?>
      </div>
      <div class="crm-agreement-scroll"><?= $body ?></div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-body">
        <div class="text-secondary small">Status</div>
        <?= status_badge($st, Contract::STATUS_COLORS, Contract::STATUSES) ?>
        <hr class="my-3">
        <dl class="row mb-0 small crm-dl">
          <dt class="col-5">Client</dt><dd class="col-7"><a href="<?= url('/clients/' . (int)$k['client_id']) ?>"><?= e(Client::displayName($k)) ?></a></dd>
          <dt class="col-5">Invoice</dt><dd class="col-7"><?= $k['invoice_id'] ? '<a href="' . url('/invoices/' . (int)$k['invoice_id']) . '">' . e($k['invoice_number']) . '</a>' : '—' ?></dd>
          <dt class="col-5">Effective</dt><dd class="col-7"><?= e(fmt_date($k['effective_date'])) ?></dd>
          <dt class="col-5">Signer</dt><dd class="col-7"><?= e($k['client_signer_name']) ?><?= $k['client_signer_email'] ? '<br><span class="text-secondary">' . e($k['client_signer_email']) . '</span>' : '' ?></dd>
          <dt class="col-5">Created by</dt><dd class="col-7"><?= e($k['created_by_name'] ?: '—') ?></dd>
        </dl>
      </div>
    </div>

    <?php if (in_array($st, ['client_signed', 'signed'], true) || $k['signed_file']): ?>
    <div class="card mb-3">
      <div class="card-header"><h3 class="card-title">Signatures</h3></div>
      <div class="list-group list-group-flush">
        <?php if ($k['client_signed_at']): ?>
        <div class="list-group-item">
          <div class="small text-secondary">Client — signed electronically</div>
          <img src="<?= e($k['client_signature']) ?>" alt="Client signature" class="crm-signature-img my-1">
          <div class="fw-medium"><?= e($k['client_signed_name']) ?><?= $k['client_signed_title'] ? ', ' . e($k['client_signed_title']) : '' ?></div>
          <div class="small text-secondary"><?= e(fmt_datetime($k['client_signed_at'])) ?> · IP <?= e($k['client_ip']) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($k['company_signed_at']): ?>
        <div class="list-group-item">
          <div class="small text-secondary">Provider — countersigned</div>
          <img src="<?= e($k['company_signature']) ?>" alt="Provider signature" class="crm-signature-img my-1">
          <div class="fw-medium"><?= e($k['company_signer_name']) ?><?= $k['company_signer_title'] ? ', ' . e($k['company_signer_title']) : '' ?></div>
          <div class="small text-secondary"><?= e(fmt_datetime($k['company_signed_at'])) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($k['signed_file']): ?>
        <div class="list-group-item">
          <div class="small text-secondary">Pen-signed copy</div>
          <a href="<?= url('/contracts/' . $id . '/signed-file') ?>"><i class="ti ti-paperclip me-1"></i>Download uploaded copy</a>
          <div class="small text-secondary"><?= e(fmt_datetime($k['signed_file_at'])) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (in_array($st, ['sent', 'client_signed', 'signed'], true)): ?>
    <div class="card mb-3">
      <div class="card-body">
        <h3 class="card-title">Signed on paper?</h3>
        <p class="small text-secondary">Download the PDF, have both parties sign it by hand, then upload the scan. The contract is marked as fully signed.</p>
        <form method="post" action="<?= url('/contracts/' . $id . '/upload') ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <input type="file" class="form-control form-control-sm mb-2" name="signed_file" accept="application/pdf,image/jpeg,image/png" required>
          <button class="btn btn-sm w-100"><i class="ti ti-upload me-1"></i>Upload signed copy</button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <div class="card mb-3">
      <div class="card-header"><h3 class="card-title">Audit trail</h3></div>
      <div class="list-group list-group-flush small">
        <?php foreach ($events as $ev): ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between gap-2">
            <span class="fw-medium"><?= e(Contract::EVENTS[$ev['event']] ?? $ev['event']) ?></span>
            <span class="text-secondary text-nowrap"><?= e(fmt_datetime($ev['created_at'])) ?></span>
          </div>
          <div class="text-secondary">
            <?= e($ev['user_name'] ?: 'Client') ?><?= $ev['ip'] ? ' · IP ' . e($ev['ip']) : '' ?>
            <?php if ($ev['details'] !== ''): ?><div class="text-break"><?= e($ev['details']) ?></div><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($st === 'draft'): ?>
    <form method="post" action="<?= url('/contracts/' . $id . '/delete') ?>" class="text-end"
          data-confirm="The draft is removed." data-confirm-title="Delete this draft?" data-confirm-button="Delete draft">
      <?= csrf_field() ?><button class="btn btn-link link-danger btn-sm p-0"><i class="ti ti-trash me-1"></i>Delete draft</button>
    </form>
    <?php elseif (is_admin() && $st !== 'cancelled'): ?>
    <div class="card border-danger-subtle">
      <div class="card-body">
        <form method="post" action="<?= url('/contracts/' . $id . '/cancel') ?>"
              data-confirm="The signing link stops working and the contract is marked as cancelled. This cannot be undone."
              data-confirm-title="Cancel this contract?" data-confirm-button="Cancel contract">
          <?= csrf_field() ?>
          <label class="form-label" for="cancel_reason">Cancel this contract</label>
          <input type="text" class="form-control form-control-sm mb-2" id="cancel_reason" name="cancel_reason" placeholder="Reason (e.g. terms changed, replaced by AGR-…)" required>
          <button class="btn btn-outline-danger btn-sm w-100"><i class="ti ti-ban me-1"></i>Cancel contract</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($st === 'client_signed' && is_admin()): ?>
<div class="modal modal-blur fade" id="countersign" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form class="modal-content" method="post" action="<?= url('/contracts/' . $id . '/countersign') ?>" id="countersign-form">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title">Countersign <?= e($k['number']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-7">
            <label class="form-label required" for="signer_name">Signer name</label>
            <input type="text" class="form-control" id="signer_name" name="signer_name" value="<?= e($s['business_signer_name'] ?: auth_user()['name']) ?>" required>
          </div>
          <div class="col-md-5">
            <label class="form-label" for="signer_title">Title</label>
            <input type="text" class="form-control" id="signer_title" name="signer_title" value="<?= e($s['business_signer_title']) ?>">
          </div>
          <div class="col-12">
            <label class="form-label required">Signature</label>
            <div class="crm-signature" data-signature-pad>
              <canvas></canvas>
              <button type="button" class="btn btn-sm btn-ghost-secondary" data-signature-clear>Clear</button>
              <input type="hidden" name="signature">
            </div>
            <div class="form-hint">Sign with the mouse, a pen or your finger. You sign on behalf of <?= e($s['business_legal_name'] ?: $s['business_name']) ?>.</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success" id="countersign-submit" disabled>Sign agreement</button>
      </div>
    </form>
  </div>
</div>
<script src="<?= asset('app/signature-pad.js') ?>"></script>
<script>
  (function () {
    var form = document.getElementById('countersign-form');
    form.addEventListener('change', function () {
      document.getElementById('countersign-submit').disabled = !form.querySelector('[name="signature"]').value;
    });
  })();
</script>
<?php endif; ?>
