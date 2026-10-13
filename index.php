<?php

declare(strict_types=1);

// ─── 1. PSR-4 Class Autoloader ───────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $len    = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative = substr($class, $len);
    $baseDir  = __DIR__ . '/';

    if (str_starts_with($relative, 'Config\\')) {
        $file = $baseDir . 'config/' . substr($relative, 7) . '.php';
    } else {
        $file = $baseDir . 'src/' . str_replace('\\', '/', $relative) . '.php';
    }

    if (file_exists($file)) {
        require_once $file;
    }
});

// ─── 2. Bootstrap ─────────────────────────────────────────────────────────────
session_start();

use App\Config\Database;
use App\Utils\View;
use App\Controllers\AuthController;
use App\Controllers\ProfileController;
use App\Controllers\AcademicController;
use App\Controllers\RequestController;
use App\Controllers\AdminController;
use App\Controllers\RegistrarController;
use App\Controllers\CashierController;
use App\Controllers\DepartmentController;

View::init(__DIR__);

$userRole = strtolower(trim((string)($_SESSION['role'] ?? 'Student')));
$isAdmin  = $userRole !== 'student';

// Handle database initialization / re-seed trigger
if (($_GET['action'] ?? '') === 'init_db') {
    $ok   = Database::initializeSchema();
    $page = $_GET['page'] ?? ($isAdmin ? 'admin-users' : 'profile');
    header("Location: index.php?page={$page}&db_status=" . ($ok ? 'ready' : 'failed'));
    exit;
}

// ─── 3. Authentication Routing ────────────────────────────────────────────────
$authController = new AuthController();

if (empty($_SESSION['signed_in'])) {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'login') {
        $authController->handleLogin();
    } else {
        $authController->showLogin();
    }
    exit;
}

// ─── 4. Role-based Routing & Whitelist Validation ─────────────────────────────
$defaultPage = match ($userRole) {
    'student'         => 'profile',
    'registrar'       => 'enrollment',
    'cashier'         => 'payments',
    'departmentstaff' => 'clearance',
    default           => 'admin-users',
};

// Required system pages from specifications
$studentPages = ['profile', 'grades', 'subjects', 'requests'];
$adminPages = match ($userRole) {
    'registrar'       => ['enrollment', 'grades-encoding', 'admin-requests'],
    'cashier'         => ['payments', 'receipts'],
    'departmentstaff' => ['clearance'],
    default           => ['admin-users', 'enrollment', 'grades-encoding', 'payments', 'receipts', 'clearance', 'admin-requests'],
};

$allowedPages = $isAdmin ? $adminPages : $studentPages;
$rawPage = trim((string)($_GET['page'] ?? ''));
$page = in_array($rawPage, $allowedPages, true) ? $rawPage : $defaultPage;

// ─── 5. Action Handlers (POST / State Changes) ────────────────────────────────
$action = trim((string)($_POST['action'] ?? $_GET['action'] ?? ''));

$studentActions = ['submit_request', 'cancel_request', 'change_password', 'logout'];
$adminActions   = [
    'add_user',
    'update_request_status',
    'enroll_student',
    'drop_enrollment',
    'encode_grade',
    'record_payment',
    'update_clearance',
    'add_clearance',
    'logout',
];

$allowedActions = $isAdmin ? $adminActions : $studentActions;

if ($action !== '') {
    if (!in_array($action, $allowedActions, true)) {
        header('Location: ?page=' . $defaultPage);
        exit;
    }

    match ($action) {
        'logout'                => $authController->handleLogout(),
        'submit_request'        => (new RequestController())->submit(),
        'cancel_request'        => (new RequestController())->cancel(),
        'change_password'       => (new ProfileController())->updatePassword(),
        'add_user'              => (new AdminController())->addUser(),
        'update_request_status' => (new AdminController())->updateRequestStatus(),
        'enroll_student'        => (new RegistrarController())->enrollStudent(),
        'drop_enrollment'       => (new RegistrarController())->dropEnrollment(),
        'encode_grade'          => (new RegistrarController())->encodeGrade(),
        'record_payment'        => (new CashierController())->recordPayment(),
        'update_clearance'      => (new DepartmentController())->updateClearance(),
        'add_clearance'         => (new DepartmentController())->addClearance(),
        default                 => null,
    };
}

// ─── 6. View Dispatcher (Page Routing) ────────────────────────────────────────
match ($page) {
    'profile'         => (new ProfileController())->index(),
    'grades'          => (new AcademicController())->grades(),
    'subjects'        => (new AcademicController())->subjects(),
    'requests'        => (new RequestController())->index(),
    'admin-users'     => (new AdminController())->users(),
    'enrollment'      => (new RegistrarController())->enrollment(),
    'grades-encoding' => (new RegistrarController())->gradesEncoding(),
    'payments'        => (new CashierController())->payments(),
    'receipts'        => (new CashierController())->receipts(),
    'clearance'       => (new DepartmentController())->clearance(),
    'admin-requests'  => (new AdminController())->requests(),
    default           => match ($defaultPage) {
        'enrollment'      => (new RegistrarController())->enrollment(),
        'payments'        => (new CashierController())->payments(),
        'clearance'       => (new DepartmentController())->clearance(),
        'admin-users'     => (new AdminController())->users(),
        default           => (new ProfileController())->index(),
    },
};
