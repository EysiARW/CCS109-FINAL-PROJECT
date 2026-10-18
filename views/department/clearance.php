<?php
use App\Utils\View;
use App\Utils\Icon;

/**
 * @var array $clearances
 * @var array $departments
 * @var array $students
 * @var int|null $deptFilter
 * @var bool $statusUpdated
 * @var bool $clearanceAdded
 */
$clearedCount = 0;
$pendingCount = 0;
$holdCount    = 0;
foreach ($clearances as $cl) {
    $st = $cl['status'] ?? 'Pending';
    if ($st === 'Cleared') {
        $clearedCount++;
    } elseif ($st === 'Hold') {
        $holdCount++;
    } else {
        $pendingCount++;
    }
}
?>
<?= View::pageHeader(
    'Clearance processing',
    'Evaluate, approve, or hold student departmental clearances.',
    "<button class='button primary' onclick=\"openModal('modal-add-clearance')\">New clearance record " . Icon::plus() . "</button>"
) ?>
<?= View::dbStatusBanner() ?>

<?php if ($statusUpdated): ?>
<div class="form-alert success">
  <?= Icon::check() ?>
  <span><strong>Clearance status updated!</strong> Changes have been recorded.</span>
  <button onclick="this.parentElement.style.display='none'"><?= Icon::x() ?></button>
</div>
<?php endif; ?>

<?php if ($clearanceAdded): ?>
<div class="form-alert success">
  <?= Icon::check() ?>
  <span><strong>Student clearance record added!</strong> Initialized for the selected office.</span>
  <button onclick="this.parentElement.style.display='none'"><?= Icon::x() ?></button>
</div>
<?php endif; ?>

<div class="grades-summary-strip">
  <div class="grades-summary-item">
    <span>Cleared Students</span>
    <strong style="color:#26593b;"><?= $clearedCount ?></strong>
    <small>Approved Departmental Clearances</small>
  </div>
  <div class="grades-summary-item">
    <span>Pending Evaluation</span>
    <strong style="color:<?= $pendingCount > 0 ? '#a6631b' : '#26593b' ?>;"><?= $pendingCount ?></strong>
    <small>Awaiting Office Review</small>
  </div>
  <div class="grades-summary-item">
    <span>On Hold</span>
    <strong style="color:<?= $holdCount > 0 ? '#b42318' : '#26593b' ?>;"><?= $holdCount ?></strong>
    <small>Requires Student Settlement</small>
  </div>
</div>

<section class="card table-card">
  <div class="toolbar">
    <div class="search-input">
      <?= Icon::search() ?>
      <input type="text" id="clearance-search-input" placeholder="Search by student, ID, or department">
    </div>
    <div class="select-wrap compact-select">
      <select id="clearance-dept-select">
        <option value="">All departments</option>
        <?php foreach ($departments as $dp): ?>
          <option value="<?= strtolower(htmlspecialchars($dp['name'])) ?>"><?= htmlspecialchars($dp['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <?= Icon::chevronDown() ?>
    </div>
    <div class="select-wrap compact-select">
      <select id="clearance-status-select">
        <option value="">All statuses</option>
        <option value="cleared">Cleared</option>
        <option value="pending">Pending</option>
        <option value="hold">Hold</option>
      </select>
      <?= Icon::chevronDown() ?>
    </div>
    <span class="toolbar-caption" id="clearance-count-caption" style="margin-left:auto;"><?= count($clearances) ?> records</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Student ID</th>
          <th>Student Name</th>
          <th>Program</th>
          <th>Department / Office</th>
          <th>Status</th>
          <th>Date Cleared</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($clearances)): ?>
          <tr><td colspan="7" style="text-align:center;color:#64756A;padding:24px;">No clearance records found.</td></tr>
        <?php else: ?>
          <?php foreach ($clearances as $cl): ?>
            <tr class="cl-row" 
                data-text="<?= strtolower(htmlspecialchars($cl['student_code'] . ' ' . $cl['student_name'] . ' ' . $cl['department_name'] . ' ' . $cl['status'])) ?>"
                data-dept="<?= strtolower(htmlspecialchars($cl['department_name'])) ?>"
                data-status="<?= strtolower(htmlspecialchars($cl['status'])) ?>">
              <td class="code-cell"><?= htmlspecialchars($cl['student_code']) ?></td>
              <td><strong><?= htmlspecialchars($cl['student_name']) ?></strong></td>
              <td><?= htmlspecialchars($cl['student_program']) ?></td>
              <td><?= htmlspecialchars($cl['department_name']) ?></td>
              <td>
                <?php
                $tone = match ($cl['status']) {
                    'Cleared' => 'success',
                    'Hold'    => 'danger',
                    default   => 'warning',
                };
                ?>
                <?= View::statusBadge($cl['status'], $tone) ?>
              </td>
              <td><?= $cl['date_cleared'] ? htmlspecialchars(date('M d, Y', strtotime($cl['date_cleared']))) : '—' ?></td>
              <td>
                <div class="row-actions" style="gap:4px;flex-wrap:wrap;">
                  <?php if ($cl['status'] !== 'Cleared'): ?>
                    <form method="POST" action="?page=clearance" style="display:inline;">
                      <input type="hidden" name="action" value="update_clearance">
                      <input type="hidden" name="clearance_id" value="<?= htmlspecialchars((string)$cl['clearance_id']) ?>">
                      <input type="hidden" name="status" value="Cleared">
                      <button type="submit" class="button primary" style="padding:4px 8px;font-size:11px;min-height:28px;height:28px;white-space:nowrap;">Approve (Clear)</button>
                    </form>
                  <?php endif; ?>
                  <?php if ($cl['status'] !== 'Hold'): ?>
                    <form method="POST" action="?page=clearance" style="display:inline;">
                      <input type="hidden" name="action" value="update_clearance">
                      <input type="hidden" name="clearance_id" value="<?= htmlspecialchars((string)$cl['clearance_id']) ?>">
                      <input type="hidden" name="status" value="Hold">
                      <button type="submit" class="reject-button" style="padding:4px 8px;font-size:11px;min-height:28px;height:28px;white-space:nowrap;">Put on Hold</button>
                    </form>
                  <?php endif; ?>
                  <?php if ($cl['status'] !== 'Pending'): ?>
                    <form method="POST" action="?page=clearance" style="display:inline;">
                      <input type="hidden" name="action" value="update_clearance">
                      <input type="hidden" name="clearance_id" value="<?= htmlspecialchars((string)$cl['clearance_id']) ?>">
                      <input type="hidden" name="status" value="Pending">
                      <button type="submit" class="button outline" style="padding:4px 8px;font-size:11px;min-height:28px;height:28px;white-space:nowrap;">Set Pending</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        <tr id="clearance-empty-row" class="search-empty-row" style="display:none;">
          <td colspan="7">
            <?= Icon::search() ?>
            <strong>No clearance records found</strong>
            <span>Try searching for another student name, department office, or status filter.</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</section>

<script>
(function() {
  var search = document.getElementById('clearance-search-input');
  var deptSel = document.getElementById('clearance-dept-select');
  var statusSel = document.getElementById('clearance-status-select');
  var countCap = document.getElementById('clearance-count-caption');
  var emptyRow = document.getElementById('clearance-empty-row');
  var rows = document.querySelectorAll('.cl-row');

  function filterClearances() {
    var q = (search.value || '').toLowerCase().trim();
    var dept = (deptSel.value || '').toLowerCase().trim();
    var status = (statusSel.value || '').toLowerCase().trim();
    var matchCount = 0;

    rows.forEach(function(row) {
      var rowText = row.getAttribute('data-text') || '';
      var rowDept = row.getAttribute('data-dept') || '';
      var rowStatus = row.getAttribute('data-status') || '';

      var matchesSearch = !q || rowText.indexOf(q) !== -1;
      var matchesDept = !dept || rowDept.indexOf(dept) !== -1;
      var matchesStatus = !status || rowStatus === status;
      var show = matchesSearch && matchesDept && matchesStatus;

      row.style.display = show ? '' : 'none';
      if (show) matchCount++;
    });

    if (emptyRow) {
      emptyRow.style.display = (matchCount === 0 && rows.length > 0) ? '' : 'none';
    }
    if (countCap) {
      countCap.textContent = matchCount + ' of ' + rows.length + ' records';
    }
  }

  if (search) search.addEventListener('input', filterClearances);
  if (deptSel) deptSel.addEventListener('change', filterClearances);
  if (statusSel) statusSel.addEventListener('change', filterClearances);
})();

// Initialize searchable student combobox in clearance modal
(function() {
  var wrap = document.getElementById('cl-student-combobox');
  if (!wrap) return;
  var input = document.getElementById('cl_student_input');
  var hidden = document.getElementById('cl_student_id');
  var menu = document.getElementById('cl_student_menu');
  var items = Array.from(menu.querySelectorAll('.combobox-item'));
  var form = wrap.closest('form');

  function resolveStudent(query) {
    if (!query) return null;
    var q = query.trim().toLowerCase();
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
    hidden.value = it.getAttribute('data-id');
    input.value = (it.getAttribute('data-name') || '') + ' (' + (it.getAttribute('data-code') || '') + ')';
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

  input.addEventListener('focus', openMenu);
  input.addEventListener('click', function(e) {
    e.stopPropagation();
    openMenu();
  });

  input.addEventListener('input', function() {
    openMenu();
    filterItems(input.value);
    var match = resolveStudent(input.value);
    hidden.value = match ? match.getAttribute('data-id') : '';
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
        if (match) selectItem(match);
      }
    }
  });

  if (form) {
    form.addEventListener('submit', function() {
      if (!hidden.value && input.value) {
        var match = resolveStudent(input.value);
        if (match) hidden.value = match.getAttribute('data-id');
      }
    });
  }
})();
</script>

<!-- Add Clearance Modal -->
<div id="modal-add-clearance" class="modal-backdrop" style="display:none" onclick="if(event.target===this)closeModal('modal-add-clearance')">
  <div class="modal" onclick="event.stopPropagation()">
    <form method="POST" action="?page=clearance">
      <input type="hidden" name="action" value="add_clearance">
      <div class="modal-heading">
        <div><h2>New clearance evaluation</h2><p>Initialize or update clearance record for a student in an office.</p></div>
        <button type="button" onclick="closeModal('modal-add-clearance')"><?= Icon::x() ?></button>
      </div>
      <div class="modal-grid">
        <div class="field">
          <label for="cl_student_input">Student</label>
          <div class="combobox-wrap" id="cl-student-combobox">
            <input type="hidden" name="student_id" id="cl_student_id">
            <input type="text" id="cl_student_input" name="student_search" class="combobox-input" placeholder="Search by name or student ID..." autocomplete="off" required>
            <ul class="combobox-menu" id="cl_student_menu">
              <?php foreach ($students as $st): ?>
                <li class="combobox-item"
                    data-id="<?= htmlspecialchars((string)$st['id']) ?>"
                    data-code="<?= htmlspecialchars($st['code']) ?>"
                    data-name="<?= htmlspecialchars($st['name']) ?>"
                    data-label="<?= htmlspecialchars($st['code'] . ' — ' . $st['name']) ?>"
                    data-search="<?= strtolower(htmlspecialchars($st['code'] . ' ' . $st['name'] . ' ' . $st['program'])) ?>">
                  <div>
                    <strong><?= htmlspecialchars($st['name']) ?></strong>
                    <div style="font-size:11px;color:var(--muted);"><?= htmlspecialchars($st['program']) ?></div>
                  </div>
                  <small><?= htmlspecialchars($st['code']) ?></small>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
        <div class="field">
          <label for="cl_department">Department / Office</label>
          <div class="select-wrap">
            <select id="cl_department" name="department_id" required>
              <option value="">Select office</option>
              <?php foreach ($departments as $dp): ?>
                <option value="<?= htmlspecialchars((string)$dp['id']) ?>"><?= htmlspecialchars($dp['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <?= Icon::chevronDown() ?>
          </div>
        </div>
        <div class="field">
          <label for="cl_status">Initial status</label>
          <div class="select-wrap">
            <select id="cl_status" name="status">
              <option value="Pending" selected>Pending</option>
              <option value="Cleared">Cleared</option>
              <option value="Hold">Hold</option>
            </select>
            <?= Icon::chevronDown() ?>
          </div>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="button outline" onclick="closeModal('modal-add-clearance')">Cancel</button>
        <button type="submit" class="button primary">Save clearance</button>
      </div>
    </form>
  </div>
</div>
