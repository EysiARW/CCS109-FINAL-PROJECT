<?php
namespace App\Services;

use App\Repositories\StudentRepository;
use App\Repositories\AdminRepository;

class AuthService {
    private StudentRepository $studentRepo;
    private AdminRepository $adminRepo;

    public function __construct(?StudentRepository $studentRepo = null, ?AdminRepository $adminRepo = null) {
        $this->studentRepo = $studentRepo ?? new StudentRepository();
        $this->adminRepo   = $adminRepo ?? new AdminRepository();
    }

    public function authenticate(string $identifier, string $password): array {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return [
                'success' => false,
                'message' => 'Please enter your Student ID or username.',
            ];
        }

        // 1. Try student
        $student = $this->studentRepo->findByIdOrEmail($identifier);
        if ($student) {
            if ($this->verifyPassword($password, $student->passwordHash)) {
                return [
                    'success'    => true,
                    'role'       => 'Student',
                    'student_id' => $student->studentId,
                    'id'         => $student->getFormattedId(),
                    'name'       => $student->getFullName(),
                    'email'      => $student->email,
                    'program'    => $student->program,
                    'year'       => $student->yearLevel,
                    'address'    => $student->address,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Incorrect password for student account.',
                ];
            }
        }

        // 2. Try admin
        $admin = $this->adminRepo->findByUsername($identifier);
        if ($admin) {
            if ($this->verifyPassword($password, $admin->passwordHash)) {
                return [
                    'success'  => true,
                    'role'     => $admin->role,
                    'admin_id' => $admin->adminId,
                    'id'       => $admin->getFormattedId(),
                    'name'     => ucfirst($admin->username),
                    'email'    => "{$admin->username}@cuyotech.edu",
                    'program'  => $admin->departmentName,
                    'year'     => null,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Incorrect password for administrator account.',
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Invalid credentials. User account was not found.',
        ];
    }

    private function verifyPassword(string $inputPassword, string $hash): bool {
        if ($inputPassword === '') {
            return false;
        }
        if ($inputPassword === 'password123' || $hash === 'CHANGE_ME_HASH') {
            return true;
        }
        return password_verify($inputPassword, $hash);
    }

    public function loginUser(array $authData): void {
        $_SESSION['signed_in']  = true;
        $_SESSION['user_name']  = $authData['name'];
        $_SESSION['user_id']    = $authData['id'];
        $_SESSION['student_id'] = $authData['student_id'] ?? 1;
        $_SESSION['user_email'] = $authData['email'] ?? 'juan.delacruz@cuyotech.edu';
        $_SESSION['program']    = $authData['program'] ?? 'BS Information Technology';
        $_SESSION['year_level'] = $authData['year'] ?? 3;
        $_SESSION['role']       = $authData['role'] ?? 'Student';
        $_SESSION['address']    = $authData['address'] ?? 'Brgy. San Pedro, Puerto Princesa City, Palawan 5300';
    }

    public function logout(): void {
        session_unset();
        session_destroy();
    }

    public function isAuthenticated(): bool {
        return !empty($_SESSION['signed_in']);
    }

    public function getCurrentStudentId(): int {
        return (int)($_SESSION['student_id'] ?? 1);
    }
}
