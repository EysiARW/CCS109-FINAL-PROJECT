<?php
use App\Utils\View;
use App\Utils\Icon;

/**
 * @var \App\Models\DocumentRequest[] $requests
 * @var string $selectedDoc
 * @var bool $submitted
 * @var string|null $requestError
 * @var string|null $newId
 * @var array $docs
 */
$prices = [
    'Certificate of Registration' => '₱ 50.00',
    'Transcript of Records'       => '₱ 100.00',
    'Certification'               => '₱ 75.00',
];
$total = $prices[$selectedDoc] ?? '₱ 50.00';

$turnaround = [
    'Certificate of Registration' => 'Estimated fulfillment: 1 business day · Instant verification',
    'Transcript of Records'       => 'Estimated fulfillment: 3–5 business days · Official dry seal',
    'Certification'               => 'Estimated fulfillment: 2–3 business days · Signed by Registrar',
];

// Determine active request for live status tracking
$activeReq = null;
foreach ($requests as $r) {
    if (in_array($r->status, ['Pending', 'Processing', 'Ready for release', 'Ready'])) {
        $activeReq = $r;
        break;
    }
}
if (!$activeReq && !empty($requests)) {
    $activeReq = $requests[0];
}
?>
<?= View::pageHeader('Document requests', 'Request official school documents without visiting the registrar.') ?>

<?= View::dbStatusBanner() ?>

<?php if ($submitted): ?>
<div class="form-alert success">
  <?= Icon::check() ?>
  <span><strong>Request submitted successfully!</strong> Your reference is DR-2025-<?= htmlspecialchars(str_pad((string)($newId ?? 182), 4, '0', STR_PAD_LEFT)) ?>.</span>
  <button onclick="this.parentElement.style.display='none'"><?= Icon::x() ?></button>
</div>
<?php endif; ?>

<?php if (!empty($cancelled)): ?>
<div class="form-alert success">
  <?= Icon::check() ?>
  <span><strong>Request cancelled.</strong> The document request has been updated to Cancelled.</span>
  <button onclick="this.parentElement.style.display='none'"><?= Icon::x() ?></button>
</div>
<?php endif; ?>

<?php if ($requestError === 'invalid_copies'): ?>
<div class="form-alert error">
  <?= Icon::alertCircle() ?>
  <span><strong>Invalid number of copies.</strong> Please request between 1 and 5 copies.</span>
</div>
<?php endif; ?>

<?php if ($activeReq && !in_array($activeReq->status, ['Cancelled', 'Rejected'])): ?>
  <?php
  $stepIndex = match ($activeReq->status) {
      'Pending'           => 1,
      'Processing'        => 2,
      'Ready for release', 'Ready' => 3,
      'Released'          => 5,
      default             => 0,
  };
  ?>
  <div class="active-tracker-card">
    <div class="active-tracker-header">
      <div class="active-tracker-title">
        <h3>Live Request Status: <?= htmlspecialchars($activeReq->documentType) ?></h3>
        <p>Reference: <strong class="code-cell"><?= htmlspecialchars($activeReq->getFormattedId()) ?></strong> · Requested on <?= htmlspecialchars(date('F d, Y', strtotime($activeReq->requestDate))) ?></p>
      </div>
      <div class="active-tracker-badges">
        <?= View::statusBadge($activeReq->getStatusLabel(), $activeReq->getStatusTone()) ?>
      </div>
    </div>
    <?= View::tracker($stepIndex) ?>
  </div>
<?php endif; ?>

<section class="card request-form">
  <form method="POST" action="?page=requests">
    <input type="hidden" name="action" value="submit_request">
    <div class="card-heading">
      <div><h2>New request</h2><p>Select a document and tell us how you'll use it.</p></div>
      <span class="required-note">All fields required</span>
    </div>
    <div class="doc-options">
    <?php foreach ($docs as $doc):
        $id  = 'doc_' . preg_replace('/\W+/', '_', $doc[0]);
        $sel = ($selectedDoc === $doc[0]);
        $eta = $turnaround[$doc[0]] ?? 'Estimated fulfillment: 2–3 business days';
    ?>
      <input type="radio" class="doc-option-input" name="doc_type" id="<?= $id ?>" value="<?= htmlspecialchars($doc[0]) ?>" <?= $sel ? 'checked' : '' ?>>
      <label for="<?= $id ?>" class="doc-option">
        <span class="radio-mark"><span class="radio-inner"></span></span>
        <span class="doc-copy">
          <strong><?= htmlspecialchars($doc[0]) ?></strong>
          <small><?= htmlspecialchars($doc[1]) ?></small>
          <small style="display:block;margin-top:4px;color:var(--sage-dark);font-weight:600;font-size:11px;"><?= htmlspecialchars($eta) ?></small>
        </span>
        <span class="doc-price"><?= htmlspecialchars($doc[2]) ?><small>per copy</small></span>
      </label>
    <?php endforeach; ?>
    </div>
    <div class="form-grid">
      <div class="field">
        <label for="purpose">Purpose</label>
        <div class="select-wrap">
          <select id="purpose" name="purpose">
            <option>Scholarship application</option>
            <option>Employment</option>
            <option>Personal copy</option>
          </select>
          <?= Icon::chevronDown() ?>
        </div>
      </div>
      <div class="field">
        <label for="copies">Copies</label>
        <input type="number" id="copies" name="copies" min="1" max="5" value="1">
      </div>
      <div class="field">
        <label for="delivery">Delivery method</label>
        <div class="select-wrap">
          <select id="delivery" name="delivery">
            <option>Pick up at registrar</option>
            <option>Courier delivery</option>
          </select>
          <?= Icon::chevronDown() ?>
        </div>
      </div>
    </div>
    <div class="request-footer">
      <div>
        <span>Total to pay</span>
        <strong id="request-total-price"><?= htmlspecialchars($total) ?></strong>
      </div>
      <button type="submit" class="button primary">Submit request <?= Icon::arrowRight() ?></button>
    </div>
  </form>
</section>

<section class="card table-card">
  <div class="card-heading">
    <div><h2>Request history</h2><p>View the progress and status of your requested university credentials.</p></div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Request</th><th>Document</th><th>Copies</th><th>Fee</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php if (empty($requests)): ?>
          <tr><td colspan="6" style="text-align:center;color:#64756A;padding:24px;">No requests submitted yet.</td></tr>
        <?php else: ?>
          <?php foreach ($requests as $rq): ?>
            <tr>
              <td class="code-cell"><?= htmlspecialchars($rq->getFormattedId()) ?></td>
              <td><?= htmlspecialchars($rq->documentType) ?></td>
              <td><?= htmlspecialchars((string)$rq->copies) ?></td>
              <td><?= htmlspecialchars($rq->getFee()) ?></td>
              <td><?= View::statusBadge($rq->getStatusLabel(), $rq->getStatusTone()) ?></td>
              <td>
                <?php if ($rq->status === 'Pending'): ?>
                  <form method="POST" action="?page=requests" style="display:inline;">
                    <input type="hidden" name="action" value="cancel_request">
                    <input type="hidden" name="request_id" value="<?= htmlspecialchars((string)$rq->requestId) ?>">
                    <button type="button" class="cancel-button" onclick="confirmCancelRequest(this.form, '<?= htmlspecialchars($rq->getFormattedId()) ?>')">Cancel</button>
                  </form>
                <?php else: ?>
                  <span style="color:#64756a;">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<script>
function confirmCancelRequest(form, id) {
  showConfirmModal({
    title: 'Cancel Document Request',
    message: 'Are you sure you want to cancel request ' + id + '? This action cannot be undone.',
    confirmText: 'Yes, cancel request',
    tone: 'danger',
    onConfirm: function() {
      form.submit();
    }
  });
}
</script>
