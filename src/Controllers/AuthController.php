<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Utils\View;

class AuthController {
    private AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function showLogin(): void {
        View::render('auth/login', [
            'pageTitle' => 'Sign in — CuyoTech SSIS',
        ], 'layouts/auth');
    }

    public function handleLogin(): void {
        $username = trim($_POST['student_id'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $result = $this->authService->authenticate($username, $password);
        if ($result['success']) {
            $this->authService->loginUser($result);
            $role = strtolower(trim((string)($result['role'] ?? 'Student')));
            $landingPage = match ($role) {
                'student'         => 'profile',
                'registrar'       => 'enrollment',
                'cashier'         => 'payments',
                'departmentstaff' => 'clearance',
                default           => 'admin-users',
            };
            header('Location: ?page=' . $landingPage);
            exit;
        }

        View::render('auth/login', [
            'pageTitle'    => 'Sign in — CuyoTech SSIS',
            'error'        => $result['message'] ?? 'Invalid credentials.',
            'enteredLogin' => $username,
        ], 'layouts/auth');
    }

    public function handleLogout(): void {
        $this->authService->logout();
        header('Location: index.php');
        exit;
    }
}
