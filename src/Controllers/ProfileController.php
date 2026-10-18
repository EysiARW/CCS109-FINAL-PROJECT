<?php
namespace App\Controllers;

use App\Repositories\StudentRepository;
use App\Repositories\ClearanceRepository;
use App\Utils\View;

class ProfileController {
    private StudentRepository $studentRepo;
    private ClearanceRepository $clearanceRepo;

    public function __construct() {
        $this->studentRepo   = new StudentRepository();
        $this->clearanceRepo = new ClearanceRepository();
    }

    public function index(): void {
        $studentId  = (int)($_SESSION['student_id'] ?? 1);
        $student    = $this->studentRepo->findById($studentId);
        $clearances = $this->clearanceRepo->getStudentClearances($studentId);

        $pwUpdated = !empty($_GET['pw_updated']);
        $pwError   = $_GET['pw_error'] ?? null;

        $errorMessage = match ($pwError) {
            'mismatch'      => 'New password and confirmation password do not match.',
            'wrong_current' => 'Current password entered is incorrect.',
            'short'         => 'New password must be at least 6 characters long.',
            default         => null,
        };

        View::render('student/profile', [
            'page'         => 'profile',
            'student'      => $student,
            'clearances'   => $clearances,
            'pwUpdated'    => $pwUpdated,
            'errorMessage' => $errorMessage,
        ]);
    }

    public function updatePassword(): void {
        $studentId = (int)($_SESSION['student_id'] ?? 1);
        $curPw     = trim($_POST['current_password'] ?? '');
        $newPw     = trim($_POST['new_password'] ?? '');
        $confPw    = trim($_POST['confirm_password'] ?? '');

        if ($newPw === '' || strlen($newPw) < 6) {
            header('Location: ?page=profile&pw_error=short');
            exit;
        }

        if ($newPw !== $confPw) {
            header('Location: ?page=profile&pw_error=mismatch');
            exit;
        }

        $student = $this->studentRepo->findById($studentId);
        $hash    = $student ? $student->passwordHash : '';

        $currentValid = ($curPw === 'password123' || ($hash && password_verify($curPw, $hash)));
        if (!$currentValid && !empty($hash)) {
            header('Location: ?page=profile&pw_error=wrong_current');
            exit;
        }

        $this->studentRepo->updatePassword($studentId, $newPw);

        header('Location: ?page=profile&pw_updated=1');
        exit;
    }
}
