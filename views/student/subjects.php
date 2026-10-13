<?php
use App\Utils\View;

/**
 * @var \App\Models\Course[] $courses
 */
?>
<?= View::pageHeader('Subjects', 'First semester · Academic year 2025–2026 · 18 total units') ?>
<?= View::dbStatusBanner() ?>

<section class="card table-card">
  <div class="card-heading">
    <div><h2>Enrolled subjects</h2><p>BS Information Technology · Year 3 · Academic Year 2025–2026</p></div>
    <span class="muted-label"><?= count($courses) ?> subjects</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Code</th><th>Subject</th><th>Units</th><th>Schedule</th><th>Room</th><th>Instructor</th></tr></thead>
      <tbody>
        <?php foreach ($courses as $c): ?>
          <tr>
            <td class="code-cell"><?= htmlspecialchars($c->courseCode) ?></td>
            <td><?= htmlspecialchars($c->courseName) ?></td>
            <td><?= htmlspecialchars((string)$c->units) ?></td>
            <td><?= htmlspecialchars($c->schedule) ?></td>
            <td><?= htmlspecialchars($c->room) ?></td>
            <td><?= htmlspecialchars($c->instructor) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
