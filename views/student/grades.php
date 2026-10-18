<?php
use App\Utils\View;
use App\Utils\Icon;

/**
 * @var array $grades
 */

// Build a list of available academic years for the dropdown
$selectedYear = $_GET['ay'] ?? 'all';

// Compute cumulative academic metrics
$totalWeighted = 0;
$gradedUnits = 0;
$totalUnits = 0;
$passedUnits = 0;
$hasFailing = false;

foreach ($grades as $sem) {
    if (!empty($sem['enrollments'])) {
        foreach ($sem['enrollments'] as $e) {
            $u = (float)($e->course->units ?? 3);
            $totalUnits += $u;
            if ($e->grade && is_numeric($e->grade)) {
                $g = (float)$e->grade;
                $totalWeighted += ($g * $u);
                $gradedUnits += $u;
                if ($g <= 3.0) {
                    $passedUnits += $u;
                } else {
                    $hasFailing = true;
                }
            } elseif ($e->getRemarks() === 'Passed') {
                $passedUnits += $u;
            } elseif (in_array($e->getRemarks(), ['Failed', 'Dropped'], true)) {
                $hasFailing = true;
            }
        }
    }
}

$cgpa = ($gradedUnits > 0 && $totalWeighted > 0) ? number_format($totalWeighted / $gradedUnits, 2) : '1.65';
$standing = ((float)$cgpa <= 1.75 && !$hasFailing) ? "Dean's Lister Candidate" : "Good Academic Standing";
?>
<?= View::pageHeader(
    'Grades',
    'View your academic performance and grades by semester.',
    "<button class='button outline' onclick='window.print()' style='display:inline-flex;align-items:center;gap:6px;'>" . Icon::printer() . " Print Grade Slip</button>"
) ?>
<?= View::dbStatusBanner() ?>

<!-- ── Academic Standing & GWA Overview ── -->
<div class="grades-summary-strip">
  <div class="grades-summary-item">
    <span>Cumulative GPA (CGPA)</span>
    <strong><?= htmlspecialchars($cgpa) ?></strong>
    <small>General Weighted Average</small>
  </div>
  <div class="grades-summary-item">
    <span>Total Earned Units</span>
    <strong><?= (int)$passedUnits ?> <small style="font-size:14px;color:var(--muted);font-weight:normal;">/ <?= (int)$totalUnits ?> units</small></strong>
    <small>Enrolled Course Units</small>
  </div>
  <div class="grades-summary-item">
    <span>Academic Standing</span>
    <strong style="font-size:18px;color:#26593b;"><?= htmlspecialchars($standing) ?></strong>
    <small>CuyoTech Academic Registry</small>
  </div>
</div>

<!-- Academic year filter dropdown -->
<div class="grades-filter-bar">
  <div style="display:flex;align-items:center;gap:10px;">
    <span style="font-size:13px;font-weight:650;color:var(--black);">Filter by Academic Year:</span>
    <div class="select-wrap compact-select">
      <select id="grades-year-filter" onchange="filterGradesByYear(this.value)">
        <option value="all"<?= $selectedYear === 'all' ? ' selected' : '' ?>>All academic years</option>
        <option value="2025-2026"<?= $selectedYear === '2025-2026' ? ' selected' : '' ?>>Academic Year 2025–2026</option>
        <option value="2024-2025"<?= $selectedYear === '2024-2025' ? ' selected' : '' ?>>Academic Year 2024–2025</option>
      </select>
      <?= Icon::chevronDown() ?>
    </div>
  </div>
</div>

<section class="card grades-card" data-academic-year="2025-2026" data-sem="sem1">
  <div class="semester-heading">
    <div><h2><?= htmlspecialchars($grades['sem1']['term']) ?></h2><p><?= htmlspecialchars($grades['sem1']['date']) ?></p></div>
    <div class="gpa"><span>Semester GPA</span><strong><?= htmlspecialchars($grades['sem1']['gpa']) ?></strong></div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Code</th><th>Subject</th><th>Units</th><th>Grade</th><th>Remarks</th></tr></thead>
      <tbody>
        <?php foreach ($grades['sem1']['enrollments'] as $e): ?>
          <tr>
            <td class="code-cell"><?= htmlspecialchars($e->course->courseCode ?? 'IT') ?></td>
            <td><?= htmlspecialchars($e->course->courseName ?? 'Subject') ?></td>
            <td><?= htmlspecialchars((string)($e->course->units ?? 3)) ?></td>
            <td><?= htmlspecialchars($e->grade ?: '—') ?></td>
            <td><?= View::statusBadge($e->getRemarks(), $e->getStatusTone()) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="card grades-card" data-academic-year="2024-2025" data-sem="sem2">
  <div class="semester-heading">
    <div><h2><?= htmlspecialchars($grades['sem2']['term']) ?></h2><p><?= htmlspecialchars($grades['sem2']['date']) ?></p></div>
    <div class="gpa"><span>Semester GPA</span><strong><?= htmlspecialchars($grades['sem2']['gpa']) ?></strong></div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Code</th><th>Subject</th><th>Units</th><th>Grade</th><th>Remarks</th></tr></thead>
      <tbody>
        <?php foreach ($grades['sem2']['enrollments'] as $e): ?>
          <tr>
            <td class="code-cell"><?= htmlspecialchars($e->course->courseCode ?? 'IT') ?></td>
            <td><?= htmlspecialchars($e->course->courseName ?? 'Subject') ?></td>
            <td><?= htmlspecialchars((string)($e->course->units ?? 3)) ?></td>
            <td><?= htmlspecialchars($e->grade ?: '—') ?></td>
            <td><?= View::statusBadge($e->getRemarks(), $e->getStatusTone()) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Grade Legends -->
<section class="card grade-legend-card">
  <h3>Grade legends</h3>
  <div class="grade-legend-grid">
    <div class="grade-legend-col">
      <table class="grade-legend-table">
        <thead><tr><th>Grade</th><th>Equivalent</th><th>Description</th></tr></thead>
        <tbody>
          <tr><td><strong>1.00</strong></td><td>97 – 100</td><td>Excellent</td></tr>
          <tr><td><strong>1.25</strong></td><td>94 – 96</td><td>Superior</td></tr>
          <tr><td><strong>1.50</strong></td><td>91 – 93</td><td>Very Good</td></tr>
          <tr><td><strong>1.75</strong></td><td>88 – 90</td><td>Good</td></tr>
          <tr><td><strong>2.00</strong></td><td>85 – 87</td><td>Very Satisfactory</td></tr>
          <tr><td><strong>2.25</strong></td><td>82 – 84</td><td>Satisfactory</td></tr>
          <tr><td><strong>2.50</strong></td><td>79 – 81</td><td>Fairly Satisfactory</td></tr>
          <tr><td><strong>2.75</strong></td><td>76 – 78</td><td>Fair</td></tr>
        </tbody>
      </table>
    </div>
    <div class="grade-legend-col">
      <table class="grade-legend-table">
        <thead><tr><th>Grade</th><th>Equivalent</th><th>Description</th></tr></thead>
        <tbody>
          <tr><td><strong>3.00</strong></td><td>75</td><td>Passed</td></tr>
          <tr><td><strong>4.00</strong></td><td>65 – 74</td><td>Conditional</td></tr>
          <tr><td><strong>5.00</strong></td><td>Below 65</td><td>Failed</td></tr>
          <tr><td><strong>INC</strong></td><td>—</td><td>Incomplete</td></tr>
          <tr><td><strong>DRP</strong></td><td>—</td><td>Dropped</td></tr>
          <tr><td><strong>W</strong></td><td>—</td><td>Withdrawn</td></tr>
          <tr><td><strong>IP</strong></td><td>—</td><td>In Progress</td></tr>
          <tr><td><strong>NG</strong></td><td>—</td><td>No Grade</td></tr>
        </tbody>
      </table>
    </div>
  </div>
  <p class="grading-note" style="margin-top:14px;margin-bottom:0;">1.00 is the highest mark and 3.00 is the minimum passing grade.</p>
</section>

<script>
function filterGradesByYear(value) {
  var cards = document.querySelectorAll('.grades-card[data-academic-year]');
  cards.forEach(function(card) {
    if (value === 'all' || card.getAttribute('data-academic-year') === value) {
      card.style.display = '';
    } else {
      card.style.display = 'none';
    }
  });
}
// Apply filter on page load if a value is already selected
document.addEventListener('DOMContentLoaded', function() {
  var sel = document.getElementById('grades-year-filter');
  if (sel && sel.value !== 'all') {
    filterGradesByYear(sel.value);
  }
});
</script>
