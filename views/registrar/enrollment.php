<?php
use App\Utils\View;
use App\Utils\Icon;

/**
 * @var array $enrollments
 * @var array $courses
 * @var array $students
 * @var string|null $schoolYear
 * @var string|null $semester
 * @var bool $enrolled
 * @var bool $dropped
 * @var string|null $errorMessage
 */
?>
<?= View::pageHeader(
    'Enrollment',
    'Manage student course registrations and curriculum enrollment.',
    "<button class='button primary' onclick=\"openModal('modal-enroll-student')\">Enroll student " . Icon::plus() . "</button>"
) ?>
<?= View::dbStatusBanner() ?>

<?php if ($enrolled): ?>
<div class="form-alert success">
  <?= Icon::check() ?>
  <span><strong>Student enrolled successfully!</strong> Registration completed.</span>
  <button onclick="this.parentElement.style.display='none'"><?= Icon::x() ?></button>
</div>
<?php endif; ?>

<?php if ($dropped): ?>
<div class="form-alert success">
  <?= Icon::check() ?>
  <span><strong>Course dropped.</strong> Student enrollment record removed.</span>
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

<div class="grades-summary-strip">
  <div class="grades-summary-item">
    <span>Total Enrollments</span>
    <strong><?= count($enrollments) ?></strong>
    <small>Registered Student Subjects</small>
  </div>
  <div class="grades-summary-item">
    <span>Active Students</span>
    <strong><?= count($students) ?></strong>
    <small>Eligible for Course Registration</small>
  </div>
  <div class="grades-summary-item">
    <span>Available Courses</span>
    <strong style="color:#26593b;"><?= count($courses) ?></strong>
    <small>Curriculum Subject Catalog</small>
  </div>
</div>

<section class="card table-card">
  <div class="toolbar">
    <div class="search-input">
      <?= Icon::search() ?>
      <input type="text" id="enrollment-search-input" placeholder="Search by student, ID, or course">
    </div>
    <div class="select-wrap compact-select">
      <select id="enrollment-term-select">
        <option value="">All terms</option>
        <option value="2025-2026 1st">2025–2026 · 1st Sem</option>
        <option value="2025-2026 2nd">2025–2026 · 2nd Sem</option>
        <option value="2024-2025 2nd">2024–2025 · 2nd Sem</option>
      </select>
      <?= Icon::chevronDown() ?>
    </div>
    <span class="toolbar-caption" id="enroll-count-caption" style="margin-left:auto;"><?= count($enrollments) ?> records</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Student ID</th>
          <th>Student Name</th>
          <th>Program</th>
          <th>Course</th>
          <th>Units</th>
          <th>Term</th>
          <th>Grade</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($enrollments)): ?>
          <tr><td colspan="8" style="text-align:center;color:#64756A;padding:24px;">No enrollment records found.</td></tr>
        <?php else: ?>
          <?php foreach ($enrollments as $en): ?>
            <tr class="enroll-row" 
                data-text="<?= strtolower(htmlspecialchars($en['student_code'] . ' ' . $en['student_name'] . ' ' . $en['course_code'] . ' ' . $en['course_name'])) ?>"
                data-term="<?= htmlspecialchars($en['school_year'] . ' ' . $en['semester']) ?>">
              <td class="code-cell"><?= htmlspecialchars($en['student_code']) ?></td>
              <td><strong><?= htmlspecialchars($en['student_name']) ?></strong></td>
              <td><?= htmlspecialchars($en['student_program']) ?></td>
              <td>
                <strong><?= htmlspecialchars($en['course_code']) ?></strong>
                <small class="table-subtext"><?= htmlspecialchars($en['course_name']) ?></small>
              </td>
              <td><?= htmlspecialchars((string)$en['units']) ?></td>
              <td><?= htmlspecialchars($en['school_year'] . ' · ' . $en['semester']) ?></td>
              <td>
                <?php if ($en['grade']): ?>
                  <span style="font-weight:600;color:#26593b;"><?= htmlspecialchars($en['grade']) ?></span>
                <?php else: ?>
                  <span style="color:#64756A;">—</span>
                <?php endif; ?>
              </td>
              <td>
                <form method="POST" action="?page=enrollment" style="display:inline;">
                  <input type="hidden" name="action" value="drop_enrollment">
                  <input type="hidden" name="enrollment_id" value="<?= htmlspecialchars((string)$en['enrollment_id']) ?>">
                  <button type="button" class="reject-button" onclick="confirmDropEnrollment(this.form, '<?= htmlspecialchars(addslashes($en['student_name'])) ?>', '<?= htmlspecialchars(addslashes($en['course_code'])) ?>')">Drop</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        <tr id="enroll-empty-row" class="search-empty-row" style="display:none;">
          <td colspan="8">
            <?= Icon::search() ?>
            <strong>No enrollment records found</strong>
            <span>Try searching for another student name, course code, or selecting another academic term.</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</section>

<script>
function confirmDropEnrollment(form, studentName, courseCode) {
  showConfirmModal({
    title: 'Drop Course Enrollment',
    message: 'Are you sure you want to drop ' + studentName + ' from ' + courseCode + '? This will permanently remove the student enrollment record.',
    confirmText: 'Yes, drop course',
    tone: 'danger',
    onConfirm: function() {
      form.submit();
    }
  });
}

(function() {
  var search = document.getElementById('enrollment-search-input');
  var termSel = document.getElementById('enrollment-term-select');
  var countCap = document.getElementById('enroll-count-caption');
  var emptyRow = document.getElementById('enroll-empty-row');
  var rows = document.querySelectorAll('.enroll-row');

  function filterEnrollments() {
    var q = (search.value || '').toLowerCase().trim();
    var term = (termSel.value || '').trim();
    var matchCount = 0;

    rows.forEach(function(row) {
      var rowText = row.getAttribute('data-text') || '';
      var rowTerm = row.getAttribute('data-term') || '';
      var matchesSearch = !q || rowText.indexOf(q) !== -1;
      var matchesTerm = !term || rowTerm.indexOf(term) !== -1;
      var show = matchesSearch && matchesTerm;

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

  if (search) search.addEventListener('input', filterEnrollments);
  if (termSel) termSel.addEventListener('change', filterEnrollments);
})();


// Initialize searchable student combobox in enroll modal
(function() {
  var wrap = document.getElementById('enr-student-combobox');
  if (!wrap) return;
  var input = document.getElementById('enr_student_input');
  var hidden = document.getElementById('enr_student_id');
  var menu = document.getElementById('enr_student_menu');
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

<!-- Enroll Student Modal -->
<div id="modal-enroll-student" class="modal-backdrop" style="display:none" onclick="if(event.target===this)closeModal('modal-enroll-student')">
  <div class="modal" onclick="event.stopPropagation()">
    <form method="POST" action="?page=enrollment">
      <input type="hidden" name="action" value="enroll_student">
      <div class="modal-heading">
        <div><h2>Enroll student</h2><p>Register a student into an academic course for the term.</p></div>
        <button type="button" onclick="closeModal('modal-enroll-student')"><?= Icon::x() ?></button>
      </div>
      <div class="modal-grid">
        <div class="field">
          <label for="enr_student_input">Student</label>
          <div class="combobox-wrap" id="enr-student-combobox">
            <input type="hidden" name="student_id" id="enr_student_id">
            <input type="text" id="enr_student_input" name="student_search" class="combobox-input" placeholder="Search by name or student ID..." autocomplete="off" required>
            <ul class="combobox-menu" id="enr_student_menu">
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
          <label for="enr_course">Course subject</label>
          <div class="select-wrap">
            <select id="enr_course" name="course_id" required>
              <option value="">Select subject</option>
              <?php foreach ($courses as $cs): ?>
                <option value="<?= htmlspecialchars((string)$cs['id']) ?>"><?= htmlspecialchars($cs['code'] . ' — ' . $cs['name'] . ' (' . $cs['units'] . ' units)') ?></option>
              <?php endforeach; ?>
            </select>
            <?= Icon::chevronDown() ?>
          </div>
        </div>
        <div class="field">
          <label for="enr_sy">School year</label>
          <div class="select-wrap">
            <select id="enr_sy" name="school_year">
              <option value="2025-2026" selected>2025–2026</option>
              <option value="2024-2025">2024–2025</option>
            </select>
            <?= Icon::chevronDown() ?>
          </div>
        </div>
        <div class="field">
          <label for="enr_sem">Semester</label>
          <div class="select-wrap">
            <select id="enr_sem" name="semester">
              <option value="1st" selected>1st semester</option>
              <option value="2nd">2nd semester</option>
              <option value="Summer">Summer</option>
            </select>
            <?= Icon::chevronDown() ?>
          </div>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="button outline" onclick="closeModal('modal-enroll-student')">Cancel</button>
        <button type="submit" class="button primary">Submit enrollment</button>
      </div>
    </form>
  </div>
</div>
