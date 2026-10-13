<?php
use App\Utils\View;
use App\Utils\Icon;

/**
 * @var array $users
 * @var bool $userAdded
 */
?>
<?= View::pageHeader(
    'User accounts',
    'Manage student and staff user access and account roles.',
    "<button class='button primary' onclick=\"openModal('modal-add-user')\">Add user " . Icon::arrowRight() . "</button>"
) ?>
<?= View::dbStatusBanner() ?>

<?php if ($userAdded): ?>
<div class="form-alert success">
  <?= Icon::check() ?>
  <span><strong>User created successfully!</strong> The new account has been activated.</span>
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
    <div class="search-input"><?= Icon::search() ?><input type="text" id="user-search-input" placeholder="Search by ID, name, or email"></div>
    <div class="select-wrap compact-select">
      <select id="user-role-select">
        <option value="">All roles</option>
        <option value="student">Student</option>
        <option value="administrator">Administrator</option>
        <option value="registrar">Registrar</option>
        <option value="cashier">Cashier</option>
      </select>
      <?= Icon::chevronDown() ?>
    </div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>ID</th><th>Name</th><th>Role</th><th>Program / Office</th><th>Status</th><th></th></tr></thead>
      <tbody id="users-table-body">
        <?php foreach ($users as $r): ?>
          <tr class="user-row" data-role="<?= strtolower(htmlspecialchars($r['role'])) ?>" data-text="<?= strtolower(htmlspecialchars($r['id'] . ' ' . $r['name'] . ' ' . $r['email'] . ' ' . $r['program'])) ?>">
            <td class="code-cell"><?= htmlspecialchars($r['id']) ?></td>
            <td>
              <strong><?= htmlspecialchars($r['name']) ?></strong>
              <small class="table-subtext"><?= htmlspecialchars($r['email']) ?></small>
            </td>
            <td><?= htmlspecialchars($r['role']) ?></td>
            <td><?= htmlspecialchars($r['program']) ?></td>
            <td><?= View::statusBadge($r['status'], $r['status'] === 'Active' ? 'success' : 'danger') ?></td>
            <td><button class="icon-button"><?= Icon::moreHorizontal() ?></button></td>
          </tr>
        <?php endforeach; ?>
        <tr id="user-empty-row" class="search-empty-row" style="display:none;">
          <td colspan="6">
            <?= Icon::search() ?>
            <strong>No user accounts found</strong>
            <span>Try searching with a different name, email, ID, or user role filter.</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</section>

<script>
(function() {
  var search = document.getElementById('user-search-input');
  var roleSel = document.getElementById('user-role-select');
  var emptyRow = document.getElementById('user-empty-row');
  var rows = document.querySelectorAll('.user-row');

  function filterUsers() {
    var q = (search.value || '').toLowerCase().trim();
    var role = (roleSel.value || '').toLowerCase();
    var matchCount = 0;

    rows.forEach(function(row) {
      var rowText = row.getAttribute('data-text') || '';
      var rowRole = row.getAttribute('data-role') || '';
      var matchesSearch = !q || rowText.indexOf(q) !== -1;
      var matchesRole = !role || rowRole === role;
      var show = matchesSearch && matchesRole;

      row.style.display = show ? '' : 'none';
      if (show) matchCount++;
    });

    if (emptyRow) {
      emptyRow.style.display = (matchCount === 0 && rows.length > 0) ? '' : 'none';
    }
  }

  if (search) search.addEventListener('input', filterUsers);
  if (roleSel) roleSel.addEventListener('change', filterUsers);
})();
</script>


<!-- Add User Modal -->
<div id="modal-add-user" class="modal-backdrop" style="display:none" onclick="if(event.target===this)closeModal('modal-add-user')">
  <div class="modal" onclick="event.stopPropagation()">
    <form method="POST" action="?page=admin-users">
      <input type="hidden" name="action" value="add_user">
      <div class="modal-heading">
        <div><h2>Add user</h2><p>Create a new student or administrative staff user account.</p></div>
        <button type="button" onclick="closeModal('modal-add-user')"><?= Icon::x() ?></button>
      </div>
      <div class="modal-grid">
        <div class="field"><label for="u_name">Full name</label><input type="text" id="u_name" name="name" placeholder="e.g. John Doe" required></div>
        <div class="field"><label for="u_email">Email address</label><input type="email" id="u_email" name="email" placeholder="name@cuyotech.edu" required></div>
        <div class="field"><label for="u_role">Role</label>
          <select id="u_role" name="role">
            <option value="Student">Student</option>
            <option value="Administrator">Administrator</option>
            <option value="Registrar">Registrar</option>
            <option value="Cashier">Cashier</option>
            <option value="DepartmentStaff">Department Staff</option>
          </select>
        </div>
        <div class="field"><label for="u_prog">Program / Office</label><input type="text" id="u_prog" name="program" placeholder="e.g. BS Information Technology"></div>
        <div class="field"><label for="u_pw">Temporary password</label><input type="password" id="u_pw" name="password" placeholder="Create temporary password"></div>
      </div>
      <div class="modal-actions">
        <button type="button" class="button outline" onclick="closeModal('modal-add-user')">Cancel</button>
        <button type="submit" class="button primary">Create account</button>
      </div>
    </form>
  </div>
</div>
