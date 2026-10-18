<?php
use App\Utils\View;
use App\Utils\Icon;

/**
 * @var \App\Models\Course[] $courses
 */
$totalUnits = 0;
foreach ($courses as $c) {
    $totalUnits += (int)($c->units ?? 3);
}
$program  = $_SESSION['program'] ?? 'BS Information Technology';
$year     = (int)($_SESSION['year_level'] ?? 3);
$yearText = match ($year) {
    1 => 'Year 1',
    2 => 'Year 2',
    3 => 'Year 3',
    4 => 'Year 4',
    default => 'Year ' . $year,
};
?>
<?= View::pageHeader(
    'Subjects',
    'First semester · Academic year 2025–2026 · ' . $totalUnits . ' total units',
    "<button class='button outline' onclick='window.print()' style='display:inline-flex;align-items:center;gap:6px;'>" . Icon::printer() . " Print Study Load</button>"
) ?>
<?= View::dbStatusBanner() ?>

<div class="grades-summary-strip">
  <div class="grades-summary-item">
    <span>Enrolled Subjects</span>
    <strong><?= count($courses) ?></strong>
    <small>Active Course Load</small>
  </div>
  <div class="grades-summary-item">
    <span>Total Units</span>
    <strong><?= $totalUnits ?> <small style="font-size:14px;color:var(--muted);font-weight:normal;">units</small></strong>
    <small>First Semester · AY 2025–2026</small>
  </div>
  <div class="grades-summary-item">
    <span>Program &amp; Year</span>
    <strong style="font-size:16px;color:#26593b;"><?= htmlspecialchars($program) ?></strong>
    <small><?= htmlspecialchars($yearText) ?> · Regular Load</small>
  </div>
</div>

<section class="card table-card">
  <div class="card-heading">
    <div><h2>Enrolled subjects</h2><p><?= htmlspecialchars($program) ?> · <?= htmlspecialchars($yearText) ?> · Academic Year 2025–2026</p></div>
    <span class="muted-label"><?= count($courses) ?> subjects</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Code</th><th>Subject</th><th>Units</th><th>Schedule</th><th>Room</th><th>Instructor</th></tr></thead>
      <tbody>
        <?php if (empty($courses)): ?>
          <tr><td colspan="6" style="text-align:center;color:#64756A;padding:24px;">No enrolled subjects found for this term.</td></tr>
        <?php else: ?>
          <?php foreach ($courses as $c): ?>
            <tr>
              <td class="code-cell"><?= htmlspecialchars($c->courseCode) ?></td>
              <td><strong><?= htmlspecialchars($c->courseName) ?></strong></td>
              <td><?= htmlspecialchars((string)$c->units) ?></td>
              <td><?= htmlspecialchars($c->schedule) ?></td>
              <td><?= htmlspecialchars($c->room) ?></td>
              <td><?= htmlspecialchars($c->instructor) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

