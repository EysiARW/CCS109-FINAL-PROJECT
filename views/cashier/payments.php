<?php
use App\Utils\View;
use App\Utils\Icon;

/**
 * @var array $payments
 * @var array $students
 * @var bool $paymentRecorded
 * @var string|null $newOr
 * @var string|null $errorMessage
 */
?>
<?= View::pageHeader(
    'Payments',
    'Record and manage student fee transactions and official receipts.',
    "<button class='button primary' onclick=\"openModal('modal-record-payment')\">Record payment " . Icon::plus() . "</button>"
) ?>
<?= View::dbStatusBanner() ?>

<?php if ($paymentRecorded): ?>
<div class="form-alert success">
  <?= Icon::check() ?>
  <span><strong>Payment recorded successfully!</strong> Official Receipt <?= htmlspecialchars($newOr ?: '') ?> issued.</span>
  <button onclick="this.parentElement.style.display='none'"><?= Icon::x() ?></button>
</div>
<?php endif; ?>

<?php if (!empty($errorMessage)): ?>
<div class="form-alert error">
  <?= Icon::alertCircle() ?>
  <span><?= htmlspecialchars($errorMessage) ?></span>
  <button onclick="this.parentElement.style.display='none'"><?= Icon::x() ?></button>
</div>
<?php endif; ?>

<section class="card table-card">
  <div class="toolbar">
    <div class="search-input">
      <?= Icon::search() ?>
      <input type="text" id="payment-search-input" placeholder="Search by OR#, student name, or ID">
    </div>
    <div class="select-wrap compact-select">
      <select id="payment-type-select">
        <option value="">All payment types</option>
        <option value="tuition">Tuition</option>
        <option value="document fee">Document Fee</option>
        <option value="miscellaneous">Miscellaneous</option>
        <option value="graduation fee">Graduation Fee</option>
      </select>
      <?= Icon::chevronDown() ?>
    </div>
    <span class="toolbar-caption" id="payment-count-caption" style="margin-left:auto;"><?= count($payments) ?> transactions</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>OR Number</th>
          <th>Date</th>
          <th>Student</th>
          <th>Program</th>
          <th>Payment Type</th>
          <th>Amount Paid</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($payments)): ?>
          <tr><td colspan="7" style="text-align:center;color:#64756A;padding:24px;">No payment transactions recorded yet.</td></tr>
        <?php else: ?>
          <?php foreach ($payments as $p): ?>
            <tr class="payment-row" 
                data-text="<?= strtolower(htmlspecialchars($p['or_number'] . ' ' . $p['student_code'] . ' ' . $p['student_name'] . ' ' . $p['payment_type'])) ?>"
                data-type="<?= strtolower(htmlspecialchars($p['payment_type'])) ?>">
              <td class="code-cell"><strong><?= htmlspecialchars($p['or_number']) ?></strong></td>
              <td><?= htmlspecialchars(date('M d, Y', strtotime($p['payment_date']))) ?></td>
              <td>
                <strong><?= htmlspecialchars($p['student_name']) ?></strong>
                <small class="table-subtext"><?= htmlspecialchars($p['student_code']) ?></small>
              </td>
              <td><?= htmlspecialchars($p['student_program']) ?></td>
              <td><?= htmlspecialchars($p['payment_type']) ?></td>
              <td><strong>₱ <?= number_format($p['amount'], 2) ?></strong></td>
              <td><?= View::statusBadge('Paid · Cleared', 'success') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        <tr id="payment-empty-row" class="search-empty-row" style="display:none;">
          <td colspan="7">
            <?= Icon::search() ?>
            <strong>No payment transactions found</strong>
            <span>Try searching for another official receipt number, student name, or ID.</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</section>

<script>
(function() {
  var search = document.getElementById('payment-search-input');
  var typeSel = document.getElementById('payment-type-select');
  var countCap = document.getElementById('payment-count-caption');
  var emptyRow = document.getElementById('payment-empty-row');
  var rows = document.querySelectorAll('.payment-row');

  function filterPayments() {
    var q = (search.value || '').toLowerCase().trim();
    var type = (typeSel.value || '').toLowerCase().trim();
    var matchCount = 0;

    rows.forEach(function(row) {
      var rowText = row.getAttribute('data-text') || '';
      var rowType = row.getAttribute('data-type') || '';
      var matchesSearch = !q || rowText.indexOf(q) !== -1;
      var matchesType = !type || rowType === type;
      var show = matchesSearch && matchesType;

      row.style.display = show ? '' : 'none';
      if (show) matchCount++;
    });

    if (emptyRow) {
      emptyRow.style.display = (matchCount === 0 && rows.length > 0) ? '' : 'none';
    }
    if (countCap) {
      countCap.textContent = matchCount + ' of ' + rows.length + ' transactions';
    }
  }

  if (search) search.addEventListener('input', filterPayments);
  if (typeSel) typeSel.addEventListener('change', filterPayments);
})();


// Initialize searchable student combobox in record payment modal
(function() {
  var wrap = document.getElementById('pay-student-combobox');
  if (!wrap) return;
  var input = document.getElementById('pay_student_input');
  var hidden = document.getElementById('pay_student_id');
  var menu = document.getElementById('pay_student_menu');
  var toggle = wrap.querySelector('.combobox-toggle');
  var items = Array.from(menu.querySelectorAll('.combobox-item'));
  var form = wrap.closest('form');

  function resolveStudent(query) {
    if (!query) return null;
    var q = query.trim().toLowerCase();
    // 1. Exact match on ID, Code, Name, or Label
    for (var i = 0; i < items.length; i++) {
      var it = items[i];
      var id = it.getAttribute('data-id') || '';
      var code = (it.getAttribute('data-code') || '').toLowerCase();
      var name = (it.getAttribute('data-name') || '').toLowerCase();
      var label = (it.getAttribute('data-label') || '').toLowerCase();
      if (id === q || code === q || name === q || label === q) {
        return it;
      }
    }
    // 2. Contains match
    for (var i = 0; i < items.length; i++) {
      var it = items[i];
      var search = (it.getAttribute('data-search') || '').toLowerCase();
      if (search.indexOf(q) !== -1) {
        return it;
      }
    }
    return null;
  }

  function selectItem(it) {
    if (!it) return;
    var id = it.getAttribute('data-id');
    var name = it.getAttribute('data-name') || '';
    var code = it.getAttribute('data-code') || '';
    hidden.value = id;
    input.value = name + ' (' + code + ')';
    items.forEach(function(el) { el.classList.remove('selected'); });
    it.classList.add('selected');
    closeMenu();
  }

  function filterItems(query) {
    var q = (query || '').trim().toLowerCase();
    var visible = 0;
    items.forEach(function(it) {
      var s = (it.getAttribute('data-search') || '').toLowerCase();
      var match = !q || s.indexOf(q) !== -1;
      it.style.display = match ? 'flex' : 'none';
      if (match) visible++;
    });
    var empty = menu.querySelector('.combobox-empty');
    if (visible === 0) {
      if (!empty) {
        empty = document.createElement('li');
        empty.className = 'combobox-empty';
        empty.textContent = 'No matching student found';
        menu.appendChild(empty);
      }
      empty.style.display = 'block';
    } else if (empty) {
      empty.style.display = 'none';
    }
  }

  function openMenu() {
    menu.classList.add('open');
    filterItems(input.value);
  }

  function closeMenu() {
    menu.classList.remove('open');
  }

  if (toggle) {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      if (menu.classList.contains('open')) {
        closeMenu();
      } else {
        openMenu();
        input.focus();
      }
    });
  }

  input.addEventListener('focus', openMenu);
  input.addEventListener('click', function(e) {
    e.stopPropagation();
    openMenu();
  });

  input.addEventListener('input', function() {
    openMenu();
    filterItems(input.value);
    var match = resolveStudent(input.value);
    if (match) {
      hidden.value = match.getAttribute('data-id');
    } else {
      hidden.value = '';
    }
  });

  items.forEach(function(it) {
    it.addEventListener('mousedown', function(e) {
      e.preventDefault();
      e.stopPropagation();
      selectItem(it);
    });
    it.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      selectItem(it);
    });
  });

  document.addEventListener('click', function(e) {
    if (!wrap.contains(e.target)) {
      closeMenu();
      if (!hidden.value && input.value) {
        var match = resolveStudent(input.value);
        if (match) {
          selectItem(match);
        }
      }
    }
  });

  if (form) {
    form.addEventListener('submit', function(e) {
      if (!hidden.value && input.value) {
        var match = resolveStudent(input.value);
        if (match) {
          hidden.value = match.getAttribute('data-id');
        }
      }
    });
  }
})();
</script>

<!-- Record Payment Modal -->
<div id="modal-record-payment" class="modal-backdrop" style="display:none" onclick="if(event.target===this)closeModal('modal-record-payment')">
  <div class="modal" onclick="event.stopPropagation()">
    <form method="POST" action="?page=payments">
      <input type="hidden" name="action" value="record_payment">
      <div class="modal-heading">
        <div><h2>Record payment</h2><p>Process a student cash or counter payment and issue an OR.</p></div>
        <button type="button" onclick="closeModal('modal-record-payment')"><?= Icon::x() ?></button>
      </div>
      <div class="modal-grid">
        <div class="field">
          <label for="pay_student_input">Student</label>
          <div class="combobox-wrap" id="pay-student-combobox">
            <input type="hidden" name="student_id" id="pay_student_id">
            <input type="text" id="pay_student_input" name="student_search" class="combobox-input" placeholder="Search by name or student ID..." autocomplete="off" required>
            <ul class="combobox-menu" id="pay_student_menu">
              <?php foreach ($students as $st): ?>
                <li class="combobox-item" 
                    data-id="<?= htmlspecialchars((string)$st['id']) ?>" 
                    data-code="<?= htmlspecialchars($st['code']) ?>" 
                    data-name="<?= htmlspecialchars($st['name']) ?>" 
                    data-label="<?= htmlspecialchars($st['code'] . ' — ' . $st['name']) ?>" 
                    data-search="<?= strtolower(htmlspecialchars($st['code'] . ' ' . $st['name'] . ' ' . ($st['program'] ?? ''))) ?>">
                  <div>
                    <strong><?= htmlspecialchars($st['name']) ?></strong>
                    <?php if (!empty($st['program'])): ?>
                      <div style="font-size:11px;color:var(--muted);"><?= htmlspecialchars($st['program']) ?></div>
                    <?php endif; ?>
                  </div>
                  <small><?= htmlspecialchars($st['code']) ?></small>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
        <div class="field">
          <label for="pay_type">Payment type</label>
          <div class="select-wrap">
            <select id="pay_type" name="payment_type" required>
              <option value="Tuition">Tuition Fee</option>
              <option value="Document Fee">Document Fee</option>
              <option value="Miscellaneous">Miscellaneous Fee</option>
              <option value="Graduation Fee">Graduation Fee</option>
            </select>
            <?= Icon::chevronDown() ?>
          </div>
        </div>
        <div class="field">
          <label for="pay_amount">Amount (₱)</label>
          <input type="number" step="0.01" min="1" id="pay_amount" name="amount" placeholder="e.g. 1250.00" required>
        </div>
        <div class="field">
          <label for="pay_orn">Official Receipt (OR) Number</label>
          <input type="text" id="pay_orn" name="or_number" placeholder="Leave blank to auto-generate (OR-2026-XXXX)">
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="button outline" onclick="closeModal('modal-record-payment')">Cancel</button>
        <button type="submit" class="button primary">Submit payment</button>
      </div>
    </form>
  </div>
</div>
