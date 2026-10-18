<?php
use App\Utils\View;
use App\Utils\Icon;

/**
 * @var \App\Models\Student|null $student
 * @var bool $pwUpdated
 */
$userName  = $student ? $student->getFullName() : ($_SESSION['user_name'] ?? 'Juan Dela Cruz');
$userEmail = $student ? $student->email : ($_SESSION['user_email'] ?? 'juan.delacruz@cuyotech.edu');
$userId    = $student ? $student->getFormattedId() : ($_SESSION['user_id'] ?? '2021-00123');
$program   = $student ? $student->program : ($_SESSION['program'] ?? 'BS Information Technology');
$year      = $student ? $student->yearLevel : ($_SESSION['year_level'] ?? 3);
$initials  = strtoupper(substr($userName, 0, 1) . substr(strrchr($userName, ' ') ?: $userName, 1, 1));
$address   = $student->address ?? ($_SESSION['address'] ?? 'Brgy. San Pedro, Puerto Princesa City, Palawan 5300');
?>
<?= View::pageHeader('Profile', 'Manage your student details and account security.') ?>
<?= View::dbStatusBanner() ?>

<?php if ($pwUpdated): ?>
<div class="form-alert success">
  <?= Icon::check() ?>
  <span><strong>Password updated successfully!</strong> Your new password has been saved.</span>
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

<div class="profile-grid">
  <section class="card">
    <div class="card-heading">
      <div><h2>Student details</h2><p>Official student records and enrolled program information.</p></div>
    </div>

    <!-- Profile picture -->
    <div class="profile-picture-section">
      <div class="profile-avatar-large" title="Profile picture">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="38" height="38" fill="currentColor">
          <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
        </svg>
      </div>
      <div class="profile-picture-info">
        <strong><?= htmlspecialchars($userName) ?></strong>
        <span><?= htmlspecialchars($program) ?></span>
        <small><?= htmlspecialchars($userId) ?></small>
      </div>
    </div>

    <dl class="detail-list">
      <?php 
      $yearText = $student ? $student->getYearLevelFormatted() : match($year) {
          1 => '1st year',
          2 => '2nd year',
          3 => '3rd year',
          4 => '4th year',
          default => '3rd year'
      };
      $details = [
        ['Student ID',     $userId],
        ['Full name',      $userName],
        ['Email address',  $userEmail],
        ['Program',        $program],
        ['Year level',     $yearText],
        ['Contact number', '+63 917 555 0182'],
        ['Address',        $address],
      ]; ?>
      <?php foreach ($details as $d): ?>
        <div><dt><?= htmlspecialchars($d[0]) ?></dt><dd><?= htmlspecialchars($d[1]) ?></dd></div>
      <?php endforeach; ?>
    </dl>
  </section>
  <section class="card">
    <div class="card-heading">
      <div><h2>Change password</h2><p>Keep your account secure with a strong password.</p></div>
      <?= Icon::keyRound() ?>
    </div>
    <form method="POST" action="?page=profile" id="form-change-password">
      <input type="hidden" name="action" value="change_password">
      <div class="field">
        <label for="cur_pw">Current password</label>
        <div class="input-action">
          <input type="password" id="cur_pw" name="current_password" required autocomplete="current-password">
          <button type="button" onclick="togglePw(this)">Show</button>
        </div>
      </div>
      <div class="field">
        <label for="new_pw">New password</label>
        <div class="input-action">
          <input type="password" id="new_pw" name="new_password" required autocomplete="new-password">
          <button type="button" onclick="togglePw(this)">Show</button>
        </div>
        <div class="pw-checklist" id="pw-checklist">
          <div class="pw-check-item" id="chk-len"><?= Icon::check() ?> 8+ characters</div>
          <div class="pw-check-item" id="chk-num"><?= Icon::check() ?> At least 1 number</div>
          <div class="pw-check-item" id="chk-sym"><?= Icon::check() ?> 1 special symbol</div>
          <div class="pw-check-item" id="chk-match"><?= Icon::check() ?> Passwords match</div>
        </div>
      </div>
      <div class="field">
        <label for="conf_pw">Confirm new password</label>
        <div class="input-action">
          <input type="password" id="conf_pw" name="confirm_password" required autocomplete="new-password">
          <button type="button" onclick="togglePw(this)">Show</button>
        </div>
      </div>
      <button type="submit" class="button primary" id="btn-update-pw">Update password</button>
    </form>
  </section>
</div>

<?php if (!empty($clearances)): ?>
<section class="card table-card" style="margin-top:24px;">
  <div class="card-heading">
    <div>
      <h2>Departmental clearance status</h2>
      <p>Live clearance evaluation across university offices required for document release and enrollment.</p>
    </div>
    <span class="muted-label"><?= count($clearances) ?> offices</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Department / Office</th>
          <th>Clearance Status</th>
          <th>Date Cleared</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clearances as $cl): ?>
          <tr>
            <td><strong><?= htmlspecialchars($cl->departmentName) ?></strong></td>
            <td><?= View::statusBadge($cl->getStatusLabel(), $cl->getStatusTone()) ?></td>
            <td><?= $cl->dateCleared ? htmlspecialchars(date('M d, Y', strtotime($cl->dateCleared))) : '—' ?></td>
            <td>
              <?php if ($cl->status === 'Cleared'): ?>
                <span style="color:#26593b;font-size:12px;font-weight:600;">No pending obligations</span>
              <?php elseif ($cl->status === 'Hold'): ?>
                <span style="color:#b42318;font-size:12px;font-weight:600;">Action required — visit office</span>
              <?php else: ?>
                <span style="color:#a6631b;font-size:12px;">Awaiting office evaluation</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php endif; ?>

<script>
(function() {
  var newPw = document.getElementById('new_pw');
  var confPw = document.getElementById('conf_pw');
  var chkLen = document.getElementById('chk-len');
  var chkNum = document.getElementById('chk-num');
  var chkSym = document.getElementById('chk-sym');
  var chkMatch = document.getElementById('chk-match');

  function validatePassword() {
    var val = newPw ? newPw.value : '';
    var conf = confPw ? confPw.value : '';

    var hasLen = val.length >= 8;
    var hasNum = /\d/.test(val);
    var hasSym = /[^A-Za-z0-9]/.test(val);
    var hasMatch = (val.length > 0 && val === conf);

    if (chkLen) chkLen.classList.toggle('valid', hasLen);
    if (chkNum) chkNum.classList.toggle('valid', hasNum);
    if (chkSym) chkSym.classList.toggle('valid', hasSym);
    if (chkMatch) chkMatch.classList.toggle('valid', hasMatch);
  }

  if (newPw) newPw.addEventListener('input', validatePassword);
  if (confPw) confPw.addEventListener('input', validatePassword);
})();
</script>

