<?php
use App\Utils\View;
use App\Utils\Icon;
?>
<main class="login-page">
  <section class="login-brand-panel">
    <?= View::brand() ?>
    <div class="login-message">
      <p class="eyebrow">Student services information system</p>
      <h1>Skip the line.<br>Do it online.</h1>
      <p>Access grades, request school documents, and manage your student account from anywhere.</p>
    </div>
    <span class="login-version">CuyoTech University · Academic Year 2025–2026</span>
  </section>
  <section class="login-form-panel">
    <div class="login-form-wrap">
      <?= View::brand(true) ?>
      <div class="login-heading">
        <h2>Sign in</h2>
        <p>Use your CuyoTech account to continue.</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="form-alert error" style="margin-bottom:18px;">
          <?= Icon::alertCircle() ?>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" action="index.php" id="login-form">
        <input type="hidden" name="action" value="login">
        <div class="field">
          <label for="student-id">Student ID or username</label>
          <input id="student-id" name="student_id" placeholder="e.g. 2021-00123 or ana.reyes" autocomplete="username" value="<?= htmlspecialchars($enteredLogin ?? '2021-00123') ?>">
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="input-action">
            <input id="password" name="password" type="password" placeholder="Enter password (default: password123)" autocomplete="current-password" value="password123">
            <button type="button" onclick="togglePw(this)">Show</button>
          </div>
        </div>
        <button type="submit" class="button primary full">Sign in <?= Icon::arrowRight() ?></button>
      </form>
    </div>
  </section>
</main>

