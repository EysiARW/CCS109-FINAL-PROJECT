<?php
use App\Utils\View;
use App\Utils\Icon;

$currentPage = $page ?? 'profile';
$userName    = $_SESSION['user_name'] ?? 'Juan Dela Cruz';
$userId      = $_SESSION['user_id'] ?? '2021-00123';
$userRole    = $_SESSION['role'] ?? 'Student';
$normalizedRole = strtolower(trim((string)$userRole));
$isAdmin     = $normalizedRole !== 'student';
$initials    = strtoupper(substr($userName, 0, 2));

// Define sections and modules according to specifications
$sections = [];

if (!$isAdmin) {
    $sections = [
        [
            'heading' => 'Student Portal',
            'items'   => [
                ['profile',   'Profile',   Icon::userRound()],
                ['grades',    'Grades',    Icon::graduationCap()],
                ['subjects',  'Subjects',  Icon::bookOpen()],
            ]
        ],
        [
            'heading' => 'Document Request System',
            'items'   => [
                ['requests',  'Document requests', Icon::fileText()],
            ]
        ]
    ];
} else {
    if ($normalizedRole === 'registrar') {
        $sections = [
            [
                'heading' => 'Registrar Module',
                'items'   => [
                    ['enrollment',      'Enrollment',      Icon::userCheck()],
                    ['grades-encoding', 'Grades encoding', Icon::edit()],
                ]
            ],
            [
                'heading' => 'Document Request System',
                'items'   => [
                    ['admin-requests',  'Document requests', Icon::fileText()],
                ]
            ]
        ];
    } elseif ($normalizedRole === 'cashier') {
        $sections = [
            [
                'heading' => 'Cashier Module',
                'items'   => [
                    ['payments',  'Payments', Icon::creditCard()],
                    ['receipts',  'Receipts', Icon::receipt()],
                ]
            ]
        ];
    } elseif ($normalizedRole === 'departmentstaff' || $normalizedRole === 'department') {
        $sections = [
            [
                'heading' => 'Department Module',
                'items'   => [
                    ['clearance', 'Clearance processing', Icon::shieldCheck()],
                ]
            ]
        ];
    } else {
        // Administrator / SuperAdmin has full access to all modules
        $sections = [
            [
                'heading' => 'Admin Module',
                'items'   => [
                    ['admin-users',     'User accounts',   Icon::users()],
                ]
            ],
            [
                'heading' => 'Registrar Module',
                'items'   => [
                    ['enrollment',      'Enrollment',      Icon::userCheck()],
                    ['grades-encoding', 'Grades encoding', Icon::edit()],
                ]
            ],
            [
                'heading' => 'Cashier Module',
                'items'   => [
                    ['payments',        'Payments',        Icon::creditCard()],
                    ['receipts',        'Receipts',        Icon::receipt()],
                ]
            ],
            [
                'heading' => 'Department Module',
                'items'   => [
                    ['clearance',       'Clearance',       Icon::shieldCheck()],
                ]
            ],
            [
                'heading' => 'Document Request System',
                'items'   => [
                    ['admin-requests',  'Document requests', Icon::fileText()],
                ]
            ]
        ];
    }
}

// Flat list for mobile navigation
$allNavItems = [];
foreach ($sections as $sec) {
    foreach ($sec['items'] as $item) {
        $allNavItems[] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(ucfirst(str_replace('-', ' ', $currentPage))) ?> — CuyoTech SSIS</title>
  <link rel="icon" type="image/png" href="CuyoTech_University_Logo.png">
  <link rel="stylesheet" href="assets/style.css?v=<?= filemtime(__DIR__ . '/../../assets/style.css') ?>">
</head>
<body>
<div class="app-shell">

  <!-- ── Sidebar ── -->
  <aside class="sidebar">
    <?= View::brand() ?>
    
    <div style="flex:1;overflow-y:auto;padding-right:4px;">
      <?php foreach ($sections as $secIndex => $sec): ?>
        <nav class="nav-group<?= $isAdmin ? ' admin-nav' : '' ?>" style="<?= $secIndex > 0 ? 'margin-top:16px;' : '' ?>">
          <span class="nav-label"><?= htmlspecialchars($sec['heading']) ?></span>
          <?php foreach ($sec['items'] as [$id, $label, $icon]):
              $active = ($currentPage === $id) ? ' active' : '';
          ?>
            <a href="?page=<?= $id ?>" class="nav-item<?= $active ?>"><?= $icon ?><?= htmlspecialchars($label) ?></a>
          <?php endforeach; ?>
        </nav>
      <?php endforeach; ?>
    </div>

    <div class="sidebar-bottom">
      <a href="?page=profile" class="user-chip" style="text-decoration:none;display:flex;" title="View Account Profile">
        <div class="avatar"><?= htmlspecialchars($initials) ?></div>
        <div>
          <strong><?= htmlspecialchars($userName) ?></strong>
          <small><?= htmlspecialchars($userRole) ?> · <?= htmlspecialchars($userId) ?></small>
        </div>
        <?= Icon::moreHorizontal() ?>
      </a>
      <a href="?action=logout" class="sign-out"><?= Icon::logOut() ?>Sign out</a>
    </div>
  </aside>

  <!-- ── Main content ── -->
  <main class="main-content">
    <!-- Mobile top bar -->
    <div class="mobile-topbar">
      <?= View::brand(true) ?>
      <button onclick="document.querySelector('.sidebar').classList.toggle('open')" aria-label="Toggle navigation menu"><?= Icon::menu() ?></button>
    </div>

    <div class="content-inner">
      <?= $content ?>
    </div>
  </main>

  <!-- ── Mobile bottom nav ── -->
  <nav class="mobile-nav">
    <?php foreach ($allNavItems as [$id, $label, $icon]):
        $short = ($label === 'Document requests') ? 'Requests' : $label;
        $active = ($currentPage === $id) ? ' active' : '';
    ?>
      <a href="?page=<?= $id ?>" class="mobile-nav-item<?= $active ?>"><?= $icon ?><span><?= htmlspecialchars($short) ?></span></a>
    <?php endforeach; ?>
  </nav>
</div>

<!-- ── Reusable Custom Confirmation Modal ── -->
<div id="modal-confirm" class="modal-backdrop" style="display:none" onclick="if(event.target===this)closeConfirmModal(false)">
  <div class="modal small-modal" onclick="event.stopPropagation()" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
    <div class="modal-heading" style="margin-bottom:16px;">
      <div>
        <h2 id="confirm-modal-title" style="font-size:18px;">Confirm Action</h2>
        <p id="confirm-modal-message">Are you sure you want to proceed?</p>
      </div>
      <button type="button" onclick="closeConfirmModal(false)"><?= Icon::x() ?></button>
    </div>
    <div class="modal-actions" style="margin-top:20px;display:flex;justify-content:flex-end;gap:8px;">
      <button type="button" class="button outline" onclick="closeConfirmModal(false)" id="confirm-modal-cancel">Cancel</button>
      <button type="button" class="button primary" id="confirm-modal-ok">Confirm</button>
    </div>
  </div>
</div>

<script>
/* Modal helpers */
function openModal(id) {
  var el = document.getElementById(id);
  if (el) {
    el.style.display = 'grid';
    var firstInput = el.querySelector('input:not([type="hidden"]), select, textarea, button.primary');
    if (firstInput) {
      setTimeout(function() { firstInput.focus(); }, 50);
    }
  }
}
function closeModal(id) {
  var el = document.getElementById(id);
  if (el) el.style.display = 'none';
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape' || e.keyCode === 27) {
    document.querySelectorAll('.modal-backdrop').forEach(function(m) {
      if (m.style.display === 'grid' || m.style.display === 'flex' || m.style.display === 'block') {
        m.style.display = 'none';
      }
    });
  }
});

/* Reusable Confirm Dialog */
var _confirmCallback = null;
function showConfirmModal(options) {
  var modal = document.getElementById('modal-confirm');
  if (!modal) return;
  document.getElementById('confirm-modal-title').textContent = options.title || 'Confirm Action';
  document.getElementById('confirm-modal-message').textContent = options.message || 'Are you sure you want to proceed?';
  var okBtn = document.getElementById('confirm-modal-ok');
  okBtn.textContent = options.confirmText || 'Confirm';
  if (options.tone === 'danger') {
    okBtn.className = 'reject-button';
    okBtn.style.padding = '8px 16px';
  } else {
    okBtn.className = 'button primary';
    okBtn.style.padding = '';
  }
  _confirmCallback = options.onConfirm || null;
  openModal('modal-confirm');
}

function closeConfirmModal(confirmed) {
  closeModal('modal-confirm');
  if (confirmed && typeof _confirmCallback === 'function') {
    var cb = _confirmCallback;
    _confirmCallback = null;
    cb();
  } else {
    _confirmCallback = null;
  }
}

document.getElementById('confirm-modal-ok')?.addEventListener('click', function() {
  closeConfirmModal(true);
});

/* Global Password Show/Hide Toggle helper */
function togglePw(btn) {
  var input = btn.previousElementSibling;
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = 'Hide';
  } else {
    input.type = 'password';
    btn.textContent = 'Show';
  }
}

/* Double-click submission prevention and loading state */
document.addEventListener('submit', function(e) {
  var form = e.target;
  if (form && !e.defaultPrevented) {
    var submitBtn = form.querySelector('button[type="submit"]:not(.no-spin)');
    if (submitBtn && !submitBtn.disabled) {
      setTimeout(function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
      }, 20);
    }
  }
});

/* Doc-option radio & copies: live price update */
(function(){
  var prices = {
    'Certificate of Registration': 50.00,
    'Transcript of Records':       100.00,
    'Certification':               75.00
  };
  var radios = document.querySelectorAll('.doc-option-input');
  var priceEl = document.getElementById('request-total-price') || document.querySelector('.request-footer strong');
  var copiesInp = document.getElementById('copies');

  function updatePrice() {
    if (!priceEl) return;
    var checked = document.querySelector('.doc-option-input:checked');
    var val = checked ? checked.value : 'Certificate of Registration';
    var unitPrice = prices[val] || 50.00;
    var count = copiesInp ? Math.max(1, parseInt(copiesInp.value, 10) || 1) : 1;
    var total = unitPrice * count;
    priceEl.textContent = '₱ ' + total.toFixed(2);
  }

  radios.forEach(function(r){
    r.addEventListener('change', updatePrice);
  });

  if (copiesInp) {
    copiesInp.addEventListener('input', updatePrice);
    copiesInp.addEventListener('change', updatePrice);
  }
})();
</script>
</body>
</html>

