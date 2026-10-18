<?php
use App\Utils\View;
use App\Utils\Icon;

/**
 * @var \App\Models\DocumentRequest[] $requests
 * @var bool $statusUpdated
 */
$activeCount   = 0;
$readyCount    = 0;
$releasedCount = 0;
foreach ($requests as $r) {
    if (in_array($r->status, ['Pending', 'Processing'], true)) {
        $activeCount++;
    } elseif (in_array($r->status, ['Ready for release', 'Ready'], true)) {
        $readyCount++;
    } elseif ($r->status === 'Released') {
        $releasedCount++;
    }
}
?>
<?= View::pageHeader('Document requests', 'Review payments and move requests through fulfillment.') ?>
<?= View::dbStatusBanner() ?>

<?php if (!empty($statusUpdated)): ?>
<div class="form-alert success">
  <?= Icon::check() ?>
  <span><strong>Request updated successfully!</strong> Status modification recorded.</span>
  <button onclick="this.parentElement.style.display='none'"><?= Icon::x() ?></button>
</div>
<?php endif; ?>

<div class="grades-summary-strip">
  <div class="grades-summary-item">
    <span>Pending &amp; Processing</span>
    <strong style="color:<?= $activeCount > 0 ? '#a6631b' : '#26593b' ?>;"><?= $activeCount ?></strong>
    <small>In Fulfillment Queue</small>
  </div>
  <div class="grades-summary-item">
    <span>Ready for Release</span>
    <strong style="color:#26593b;"><?= $readyCount ?></strong>
    <small>Awaiting Student Pickup</small>
  </div>
  <div class="grades-summary-item">
    <span>Released Credentials</span>
    <strong><?= $releasedCount ?></strong>
    <small>Completed Document Requests</small>
  </div>
</div>

<section class="card table-card">
  <div class="toolbar">
    <div class="search-input">
      <?= Icon::search() ?>
      <input type="text" id="req-search-input" placeholder="Search by student, ID, or document">
    </div>
    <div class="select-wrap compact-select">
      <select id="req-status-select">
        <option value="">All statuses</option>
        <option value="pending">Awaiting payment</option>
        <option value="processing">Processing</option>
        <option value="ready">Ready for release</option>
        <option value="released">Released</option>
        <option value="rejected">Rejected</option>
        <option value="cancelled">Cancelled</option>
      </select>
      <?= Icon::chevronDown() ?>
    </div>
    <span class="toolbar-caption" id="req-count-caption" style="margin-left:auto;"><?= count($requests) ?> total requests</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Request</th><th>Student</th><th>Document</th><th>Copies</th><th>Fee</th><th>Status</th><th>Next step</th></tr></thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
          <tr class="admin-req-row" data-status="<?= strtolower(htmlspecialchars($r->status)) ?>" data-text="<?= strtolower(htmlspecialchars($r->getFormattedId() . ' ' . ($r->studentName ?: 'Juan Dela Cruz') . ' ' . $r->documentType)) ?>">
            <td>
              <strong class="code-cell"><?= htmlspecialchars($r->getFormattedId()) ?></strong>
              <small class="table-subtext">Ref. PR-2025-<?= htmlspecialchars(str_pad((string)$r->requestId, 4, '0', STR_PAD_LEFT)) ?></small>
            </td>
            <td><?= htmlspecialchars($r->studentName ?: 'Juan Dela Cruz') ?></td>
            <td><?= htmlspecialchars($r->documentType) ?></td>
            <td><?= htmlspecialchars((string)$r->copies) ?></td>
            <td><?= htmlspecialchars($r->getFee()) ?></td>
            <td><?= View::statusBadge($r->getStatusLabel(), $r->getStatusTone()) ?></td>
            <td>
              <div class="row-actions" style="gap:4px;flex-wrap:wrap;">
                <?php if ($r->getNextAction() !== 'Completed' && $r->status !== 'Cancelled' && $r->status !== 'Rejected'): ?>
                  <form method="POST" action="?page=admin-requests" style="display:inline;">
                    <input type="hidden" name="action" value="update_request_status">
                    <input type="hidden" name="request_id" value="<?= htmlspecialchars((string)$r->requestId) ?>">
                    <input type="hidden" name="status_action" value="<?= htmlspecialchars($r->getNextAction()) ?>">
                    <button type="submit" class="button primary" style="padding:4px 8px;font-size:11px;min-height:28px;height:28px;"><?= htmlspecialchars($r->getNextAction()) ?></button>
                  </form>
                  <button type="button" class="reject-button" style="padding:4px 8px;font-size:11px;min-height:28px;height:28px;" onclick="openRejectModal(<?= htmlspecialchars((string)$r->requestId) ?>)">Reject</button>
                <?php else: ?>
                  <span style="color:#64756A;font-size:12px;"><?= htmlspecialchars($r->status) ?></span>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr id="req-empty-row" class="search-empty-row" style="display:none;">
          <td colspan="7">
            <?= Icon::search() ?>
            <strong>No requests found</strong>
            <span>Try searching with a different student name, reference ID, or status filter.</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</section>

<script>
(function() {
  var search = document.getElementById('req-search-input');
  var sel = document.getElementById('req-status-select');
  var countCap = document.getElementById('req-count-caption');
  var emptyRow = document.getElementById('req-empty-row');
  var rows = document.querySelectorAll('.admin-req-row');

  function filterRequests() {
    var q = (search ? search.value : '').toLowerCase().trim();
    var val = (sel.value || '').toLowerCase();
    var matchCount = 0;

    rows.forEach(function(row) {
      var txt = row.getAttribute('data-text') || '';
      var st = row.getAttribute('data-status') || '';
      var matchesSearch = !q || txt.indexOf(q) !== -1;
      var matchesStatus = !val || st.indexOf(val) !== -1;
      var show = matchesSearch && matchesStatus;

      row.style.display = show ? '' : 'none';
      if (show) matchCount++;
    });

    if (emptyRow) {
      emptyRow.style.display = (matchCount === 0 && rows.length > 0) ? '' : 'none';
    }
    if (countCap) {
      countCap.textContent = matchCount + ' of ' + rows.length + ' requests';
    }
  }

  if (search) search.addEventListener('input', filterRequests);
  if (sel) sel.addEventListener('change', filterRequests);
})();
</script>


<!-- Reject Modal -->
<div id="modal-reject" class="modal-backdrop" style="display:none" onclick="if(event.target===this)closeModal('modal-reject')">
  <div class="modal small-modal" onclick="event.stopPropagation()">
    <form method="POST" action="?page=admin-requests">
      <input type="hidden" name="action" value="update_request_status">
      <input type="hidden" name="status_action" value="Reject">
      <input type="hidden" name="request_id" id="reject_request_id" value="0">
      <div class="modal-heading">
        <div><h2>Reject request</h2><p>Tell the student why this request cannot be processed.</p></div>
        <button type="button" onclick="closeModal('modal-reject')"><?= Icon::x() ?></button>
      </div>
      <div class="field">
        <label for="reject_reason">Reason</label>
        <textarea id="reject_reason" name="reason" rows="4" placeholder="Enter a reason for rejection" style="resize:vertical;" required></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="button outline" onclick="closeModal('modal-reject')">Cancel</button>
        <button type="submit" class="button danger">Reject request</button>
      </div>
    </form>
  </div>
</div>
<script>
function openRejectModal(id) {
  var el = document.getElementById('reject_request_id');
  if (el) el.value = id;
  openModal('modal-reject');
}
</script>
