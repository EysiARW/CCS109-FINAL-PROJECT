<?php
use App\Utils\View;
use App\Utils\Icon;

/**
 * @var array $enrollments
 * @var array $courses
 * @var string|null $schoolYear
 * @var string|null $semester
 * @var bool $gradeUpdated
 */
$totalEnrollments = count($enrollments);
$encodedCount     = 0;
$pendingCount     = 0;
foreach ($enrollments as $en) {
    if (!empty($en['grade'])) {
        $encodedCount++;
    } else {
        $pendingCount++;
    }
}
?>
<?= View::pageHeader(
    'Grades encoding',
    'Encode and finalize student marks and academic grades.'
) ?>
<?= View::dbStatusBanner() ?>

<?php if ($gradeUpdated): ?>
<div class="form-alert success">
  <?= Icon::check() ?>
  <span><strong>Grade updated successfully!</strong> The grade mark has been saved.</span>
  <button onclick="this.parentElement.style.display='none'"><?= Icon::x() ?></button>
</div>
<?php endif; ?>

<div class="grades-summary-strip">
  <div class="grades-summary-item">
    <span>Total Course Records</span>
    <strong><?= $totalEnrollments ?></strong>
    <small>Enrolled Student Subjects</small>
  </div>
  <div class="grades-summary-item">
    <span>Grades Encoded</span>
    <strong><?= $encodedCount ?></strong>
    <small>Finalized Term Marks</small>
  </div>
  <div class="grades-summary-item">
    <span>Pending Encoding</span>
    <strong style="color:<?= $pendingCount > 0 ? '#a6631b' : '#26593b' ?>;"><?= $pendingCount ?></strong>
    <small>Awaiting Faculty Submission</small>
  </div>
</div>

<section class="card table-card">
  <div class="toolbar">
    <div class="search-input">
      <?= Icon::search() ?>
      <input type="text" id="grade-search-input" placeholder="Search by student, ID, or course">
    </div>
    <div class="select-wrap compact-select">
      <select id="grade-course-select">
        <option value="">All courses</option>
        <?php foreach ($courses as $cs): ?>
          <option value="<?= strtolower(htmlspecialchars($cs['code'])) ?>"><?= htmlspecialchars($cs['code']) ?></option>
        <?php endforeach; ?>
      </select>
      <?= Icon::chevronDown() ?>
    </div>
    <span class="toolbar-caption" id="grade-count-caption" style="margin-left:auto;"><?= count($enrollments) ?> students</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Student</th>
          <th>Course Code</th>
          <th>Course Title</th>
          <th>Term</th>
          <th>Units</th>
          <th>Current Grade</th>
          <th>Encode Grade</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($enrollments)): ?>
          <tr><td colspan="7" style="text-align:center;color:#64756A;padding:24px;">No student records found to encode.</td></tr>
        <?php else: ?>
          <?php foreach ($enrollments as $en): ?>
            <tr class="grade-row" 
                data-text="<?= strtolower(htmlspecialchars($en['student_code'] . ' ' . $en['student_name'] . ' ' . $en['course_code'] . ' ' . $en['course_name'])) ?>"
                data-course="<?= strtolower(htmlspecialchars($en['course_code'])) ?>">
              <td>
                <strong><?= htmlspecialchars($en['student_name']) ?></strong>
                <small class="table-subtext"><?= htmlspecialchars($en['student_code'] . ' · ' . $en['student_program']) ?></small>
              </td>
              <td class="code-cell"><?= htmlspecialchars($en['course_code']) ?></td>
              <td><?= htmlspecialchars($en['course_name']) ?></td>
              <td><?= htmlspecialchars($en['school_year'] . ' · ' . $en['semester']) ?></td>
              <td><?= htmlspecialchars((string)$en['units']) ?></td>
              <td>
                <?php if ($en['grade']): ?>
                  <?php
                  $gUpper = strtoupper(trim((string)$en['grade']));
                  if ($gUpper === 'INC') {
                      $gLabel = 'INC · Incomplete';
                      $gTone  = 'warning';
                  } elseif ($gUpper === 'DRP') {
                      $gLabel = 'DRP · Dropped';
                      $gTone  = 'danger';
                  } elseif (is_numeric($en['grade']) && (float)$en['grade'] <= 3.0) {
                      $gLabel = $en['grade'] . ' · Passed';
                      $gTone  = 'success';
                  } else {
                      $gLabel = $en['grade'] . ' · Failed';
                      $gTone  = 'danger';
                  }
                  ?>
                  <?= View::statusBadge($gLabel, $gTone) ?>
                <?php else: ?>
                  <?= View::statusBadge('Not encoded', 'warning') ?>
                <?php endif; ?>
              </td>
              <td>
                <form method="POST" action="?page=grades-encoding" style="display:flex;align-items:center;gap:4px;flex-wrap:nowrap;">
                  <input type="hidden" name="action" value="encode_grade">
                  <input type="hidden" name="enrollment_id" value="<?= htmlspecialchars((string)$en['enrollment_id']) ?>">
                  <div class="select-wrap" style="width:84px;flex-shrink:0;">
                    <select name="grade" class="grade-select-input" data-initial="<?= htmlspecialchars((string)($en['grade'] ?? '')) ?>" style="padding:5px 20px 5px 8px;font-size:12px;border-radius:6px;min-height:32px;height:32px;">
                      <option value="">—</option>
                      <?php 
                      $gradeList = ['1.00', '1.25', '1.50', '1.75', '2.00', '2.25', '2.50', '2.75', '3.00', '5.00', 'INC', 'DRP'];
                      foreach ($gradeList as $g): ?>
                        <option value="<?= $g ?>" <?= ($en['grade'] === $g) ? 'selected' : '' ?>><?= $g ?></option>
                      <?php endforeach; ?>
                    </select>
                    <?= Icon::chevronDown() ?>
                  </div>
                  <button type="submit" class="button primary" style="padding:5px 9px;font-size:11px;min-height:32px;height:32px;flex-shrink:0;">Save</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        <tr id="grade-empty-row" class="search-empty-row" style="display:none;">
          <td colspan="7">
            <?= Icon::search() ?>
            <strong>No student grade records found</strong>
            <span>Try adjusting your search query or selecting a different course filter.</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</section>

<script>
(function() {
  var search = document.getElementById('grade-search-input');
  var courseSel = document.getElementById('grade-course-select');
  var countCap = document.getElementById('grade-count-caption');
  var emptyRow = document.getElementById('grade-empty-row');
  var rows = document.querySelectorAll('.grade-row');

  function filterGrades() {
    var q = (search.value || '').toLowerCase().trim();
    var course = (courseSel.value || '').toLowerCase().trim();
    var matchCount = 0;

    rows.forEach(function(row) {
      var rowText = row.getAttribute('data-text') || '';
      var rowCourse = row.getAttribute('data-course') || '';
      var matchesSearch = !q || rowText.indexOf(q) !== -1;
      var matchesCourse = !course || rowCourse === course;
      var show = matchesSearch && matchesCourse;

      row.style.display = show ? '' : 'none';
      if (show) matchCount++;
    });

    if (emptyRow) {
      emptyRow.style.display = (matchCount === 0 && rows.length > 0) ? '' : 'none';
    }
    if (countCap) {
      countCap.textContent = matchCount + ' of ' + rows.length + ' students';
    }
  }

  if (search) search.addEventListener('input', filterGrades);
  if (courseSel) courseSel.addEventListener('change', filterGrades);

  // Highlight row when grade is changed by instructor
  document.querySelectorAll('.grade-select-input').forEach(function(sel) {
    sel.addEventListener('change', function() {
      var row = this.closest('.grade-row');
      var init = this.getAttribute('data-initial') || '';
      if (this.value !== init) {
        row.classList.add('is-dirty');
      } else {
        row.classList.remove('is-dirty');
      }
    });
  });
})();
</script>

