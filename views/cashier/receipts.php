<?php
use App\Utils\View;
use App\Utils\Icon;

/**
 * @var array $payments
 */
?>
<?= View::pageHeader(
    'Receipts',
    'Official Receipt (OR) directory, audit trail, and print voucher generator.'
) ?>
<?= View::dbStatusBanner() ?>

<section class="card table-card">
  <div class="toolbar">
    <div class="search-input">
      <?= Icon::search() ?>
      <input type="text" id="receipt-search-input" placeholder="Search by OR#, student name, or date">
    </div>
    <span class="toolbar-caption"><?= count($payments) ?> receipts issued</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>OR Number</th>
          <th>Date Issued</th>
          <th>Student Payer</th>
          <th>Payment Description</th>
          <th>Amount Paid</th>
          <th>Receipt Voucher</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($payments)): ?>
          <tr><td colspan="6" style="text-align:center;color:#64756A;padding:24px;">No receipts found.</td></tr>
        <?php else: ?>
          <?php foreach ($payments as $p): ?>
            <tr class="receipt-row" 
                data-text="<?= strtolower(htmlspecialchars($p['or_number'] . ' ' . $p['student_code'] . ' ' . $p['student_name'] . ' ' . $p['payment_type'])) ?>">
              <td class="code-cell"><strong><?= htmlspecialchars($p['or_number']) ?></strong></td>
              <td><?= htmlspecialchars(date('F d, Y', strtotime($p['payment_date']))) ?></td>
              <td>
                <strong><?= htmlspecialchars($p['student_name']) ?></strong>
                <small class="table-subtext"><?= htmlspecialchars($p['student_code'] . ' · ' . $p['student_program']) ?></small>
              </td>
              <td><?= htmlspecialchars($p['payment_type']) ?></td>
              <td><strong style="color:#26593b;">₱ <?= number_format($p['amount'], 2) ?></strong></td>
              <td>
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                  <button type="button" class="button outline" style="padding:5px 10px;font-size:12px;"
                          onclick="viewReceipt(
                            <?= htmlspecialchars(json_encode($p['or_number'])) ?>,
                            <?= htmlspecialchars(json_encode(date('F d, Y', strtotime($p['payment_date'])))) ?>,
                            <?= htmlspecialchars(json_encode($p['student_name'])) ?>,
                            <?= htmlspecialchars(json_encode($p['student_code'])) ?>,
                            <?= htmlspecialchars(json_encode($p['student_program'])) ?>,
                            <?= htmlspecialchars(json_encode($p['payment_type'])) ?>,
                            <?= htmlspecialchars(json_encode(number_format($p['amount'], 2))) ?>
                          )">
                    <?= Icon::receipt() ?> View
                  </button>
                  <button type="button" class="button outline" style="padding:5px 10px;font-size:12px;"
                          onclick="printReceiptDirect(
                            <?= htmlspecialchars(json_encode($p['or_number'])) ?>,
                            <?= htmlspecialchars(json_encode(date('F d, Y', strtotime($p['payment_date'])))) ?>,
                            <?= htmlspecialchars(json_encode($p['student_name'])) ?>,
                            <?= htmlspecialchars(json_encode($p['student_code'])) ?>,
                            <?= htmlspecialchars(json_encode($p['student_program'])) ?>,
                            <?= htmlspecialchars(json_encode($p['payment_type'])) ?>,
                            <?= htmlspecialchars(json_encode(number_format($p['amount'], 2))) ?>
                          )">
                    <?= Icon::printer() ?> Print
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        <tr id="receipt-empty-row" class="search-empty-row" style="display:none;">
          <td colspan="6">
            <?= Icon::search() ?>
            <strong>No receipts found</strong>
            <span>Try searching for another official receipt number, student name, or ID.</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</section>

<script>
(function() {
  var search = document.getElementById('receipt-search-input');
  var countCap = document.querySelector('.toolbar-caption');
  var emptyRow = document.getElementById('receipt-empty-row');
  var rows = document.querySelectorAll('.receipt-row');

  if (search) {
    search.addEventListener('input', function() {
      var q = (search.value || '').toLowerCase().trim();
      var matchCount = 0;
      rows.forEach(function(row) {
        var txt = row.getAttribute('data-text') || '';
        var show = (!q || txt.indexOf(q) !== -1);
        row.style.display = show ? '' : 'none';
        if (show) matchCount++;
      });
      if (emptyRow) {
        emptyRow.style.display = (matchCount === 0 && rows.length > 0) ? '' : 'none';
      }
      if (countCap) {
        countCap.textContent = matchCount + ' of ' + rows.length + ' receipts issued';
      }
    });
  }
})();

function printReceiptDirect(orn, date, name, id, prog, type, amt) {
  viewReceipt(orn, date, name, id, prog, type, amt);
  setTimeout(function() {
    window.print();
  }, 200);
}
</script>


<!-- Official Receipt Modal -->
<div id="modal-receipt-view" class="modal-backdrop" style="display:none" onclick="if(event.target===this)closeModal('modal-receipt-view')">
  <div class="modal" style="max-width:560px;" onclick="event.stopPropagation()">
    <div class="modal-heading">
      <div>
        <h2>Official Receipt</h2>
        <p>CuyoTech University · Cashier's Office</p>
      </div>
      <button type="button" onclick="closeModal('modal-receipt-view')"><?= Icon::x() ?></button>
    </div>
    <div id="printable-receipt" style="background:#ffffff;border:1px solid #cad7cf;border-radius:8px;padding:24px;margin-bottom:18px;">
      <div style="text-align:center;border-bottom:2px dashed #cad7cf;padding-bottom:14px;margin-bottom:16px;">
        <img src="CuyoTech_University_Logo_sm.png" alt="CuyoTech University Logo" style="height:46px;width:auto;max-width:120px;object-fit:contain;margin-bottom:6px;" width="64" height="46">
        <h3 style="margin:0;font-size:16px;color:#18231c;text-transform:uppercase;letter-spacing:0.5px;">CuyoTech University</h3>
        <p style="margin:2px 0 0;font-size:12px;color:#4e5f56;">Student Services Information System · Cashier's Office</p>
        <div style="display:flex;justify-content:space-between;margin-top:14px;font-size:13px;">
          <span><strong>OR Number:</strong> <span id="rec-orn" style="font-family:monospace;color:#26593b;"></span></span>
          <span><strong>Date:</strong> <span id="rec-date"></span></span>
        </div>
      </div>

      <div style="font-size:13px;line-height:1.8;">
        <div style="display:flex;justify-content:space-between;border-bottom:1px solid #edf3ef;padding:4px 0;">
          <span style="color:#4e5f56;">Received From:</span>
          <strong><span id="rec-name"></span></strong>
        </div>
        <div style="display:flex;justify-content:space-between;border-bottom:1px solid #edf3ef;padding:4px 0;">
          <span style="color:#4e5f56;">Student ID:</span>
          <span id="rec-id"></span>
        </div>
        <div style="display:flex;justify-content:space-between;border-bottom:1px solid #edf3ef;padding:4px 0;">
          <span style="color:#4e5f56;">Degree Program:</span>
          <span id="rec-prog"></span>
        </div>
        <div style="display:flex;justify-content:space-between;border-bottom:1px solid #edf3ef;padding:4px 0;">
          <span style="color:#4e5f56;">Payment Category:</span>
          <span id="rec-type"></span>
        </div>
        <div style="display:flex;justify-content:space-between;border-top:2px solid #354f46;margin-top:12px;padding-top:8px;font-size:16px;">
          <strong>Total Amount Paid:</strong>
          <strong style="color:#26593b;">₱ <span id="rec-amt"></span></strong>
        </div>
      </div>

      <div style="margin-top:24px;padding-top:14px;border-top:1px dashed #cad7cf;display:flex;justify-content:space-between;align-items:flex-end;">
        <div>
          <span style="display:inline-block;padding:2px 8px;font-size:11px;border-radius:4px;background:#ebf5ee;color:#26593b;border:1px solid #badbca;font-weight:600;">PAID & VERIFIED</span>
        </div>
        <div style="text-align:center;font-size:11px;color:#4e5f56;">
          <div style="border-bottom:1px solid #18231c;width:140px;margin-bottom:2px;"></div>
          <span>Authorized Cashier Signature</span>
        </div>
      </div>
    </div>
    <div class="modal-actions">
      <button type="button" class="button outline" onclick="closeModal('modal-receipt-view')">Close</button>
      <button type="button" class="button primary" onclick="window.print()"><?= Icon::receipt() ?> Print receipt</button>
    </div>
  </div>
</div>

<script>
function viewReceipt(orn, date, name, id, prog, type, amt) {
  document.getElementById('rec-orn').textContent = orn;
  document.getElementById('rec-date').textContent = date;
  document.getElementById('rec-name').textContent = name;
  document.getElementById('rec-id').textContent = id;
  document.getElementById('rec-prog').textContent = prog;
  document.getElementById('rec-type').textContent = type;
  document.getElementById('rec-amt').textContent = amt;
  openModal('modal-receipt-view');
}
</script>
