<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\AdminUser;
use App\Models\Student;
use PDO;

class AdminRepository {
    private ?PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findByUsername(string $username): ?AdminUser {
        $cleanUsername = trim($username);

        if (!$this->db) {
            $demoAdmins = [
                'ana.reyes' => [
                    'AdminID'        => 1,
                    'Username'       => 'ana.reyes',
                    'PasswordHash'   => '$2y$10$y12j1f6qwbPO3gNhN7D9EORi0OJw9SyWIle0qEbqCZNdK..iYfxv2',
                    'Role'           => 'Administrator',
                    'DepartmentName' => 'Registrar Office',
                ],
                'm.santiago' => [
                    'AdminID'        => 2,
                    'Username'       => 'm.santiago',
                    'PasswordHash'   => '$2y$10$y12j1f6qwbPO3gNhN7D9EORi0OJw9SyWIle0qEbqCZNdK..iYfxv2',
                    'Role'           => 'Registrar',
                    'DepartmentName' => 'Registrar Office',
                ],
                'cashier01' => [
                    'AdminID'        => 3,
                    'Username'       => 'cashier01',
                    'PasswordHash'   => '$2y$10$y12j1f6qwbPO3gNhN7D9EORi0OJw9SyWIle0qEbqCZNdK..iYfxv2',
                    'Role'           => 'Cashier',
                    'DepartmentName' => 'Cashier Office',
                ],
                'dept.staff' => [
                    'AdminID'        => 4,
                    'Username'       => 'dept.staff',
                    'PasswordHash'   => '$2y$10$y12j1f6qwbPO3gNhN7D9EORi0OJw9SyWIle0qEbqCZNdK..iYfxv2',
                    'Role'           => 'DepartmentStaff',
                    'DepartmentName' => 'College of Computing Studies',
                ],
            ];

            $lower = strtolower($cleanUsername);
            if (isset($demoAdmins[$lower])) {
                return new AdminUser($demoAdmins[$lower]);
            }

            if (!empty($_SESSION['custom_admins'])) {
                foreach ($_SESSION['custom_admins'] as $ca) {
                    if (strcasecmp($ca['Username'] ?? '', $cleanUsername) === 0) {
                        return new AdminUser($ca);
                    }
                }
            }

            return null;
        }

        $stmt = $this->db->prepare("
            SELECT a.*, d.DepartmentName 
            FROM AdminUser a
            LEFT JOIN Department d ON a.DepartmentID = d.DepartmentID
            WHERE a.Username = :u LIMIT 1
        ");
        $stmt->execute([':u' => $cleanUsername]);
        $row = $stmt->fetch();

        return $row ? new AdminUser($row) : null;
    }

    /**
     * @return array<int, array{id: string, name: string, role: string, program: string, status: string, email: string}>
     */
    public function getAllUsers(): array {
        $users = [];

        if (!$this->db) {
            $users = [
                ['id' => '2021-00123', 'name' => 'Juan Dela Cruz', 'role' => 'Student',       'program' => 'BS Information Technology', 'status' => 'Active',      'email' => 'juan.delacruz@cuyotech.edu'],
                ['id' => '2024-00412', 'name' => 'Maria Santos',   'role' => 'Student',       'program' => 'BS Accountancy',            'status' => 'Active',      'email' => '2024-00412@cuyotech.edu'],
                ['id' => 'admin-0001', 'name' => 'Ana Reyes',      'role' => 'Administrator', 'program' => 'Registrar Office',          'status' => 'Active',      'email' => 'ana.reyes@cuyotech.edu'],
                ['id' => '2020-00087', 'name' => 'Paolo Gomez',    'role' => 'Student',       'program' => 'BS Information Technology', 'status' => 'Deactivated', 'email' => '2020-00087@cuyotech.edu'],
                ['id' => 'admin-0002', 'name' => 'M. Santiago',    'role' => 'Registrar',     'program' => 'Registrar Office',          'status' => 'Active',      'email' => 'm.santiago@cuyotech.edu'],
                ['id' => 'admin-0003', 'name' => 'Cashier 01',     'role' => 'Cashier',       'program' => 'Cashier Office',            'status' => 'Active',      'email' => 'cashier01@cuyotech.edu'],
            ];

            if (!empty($_SESSION['custom_users'])) {
                foreach ($_SESSION['custom_users'] as $cu) {
                    $users[] = $cu;
                }
            }

            return $users;
        }

        // Students
        $stmt = $this->db->query("SELECT StudentID, FirstName, LastName, Email, Program, Status FROM Student ORDER BY StudentID ASC");
        while ($s = $stmt->fetch()) {
            $student = new Student($s);
            $users[] = [
                'id'      => $student->getFormattedId(),
                'name'    => $student->getFullName(),
                'role'    => 'Student',
                'program' => $student->program ?: 'General Program',
                'status'  => $student->status,
                'email'   => $student->email,
            ];
        }

        // Admins
        $stmt = $this->db->query("
            SELECT a.*, d.DepartmentName 
            FROM AdminUser a
            LEFT JOIN Department d ON a.DepartmentID = d.DepartmentID
            ORDER BY a.AdminID ASC
        ");
        while ($a = $stmt->fetch()) {
            $admin = new AdminUser($a);
            $users[] = [
                'id'      => $admin->getFormattedId(),
                'name'    => ucfirst($admin->username),
                'role'    => $admin->role,
                'program' => $admin->departmentName ?: 'Administrative Staff',
                'status'  => 'Active',
                'email'   => "{$admin->username}@cuyotech.edu",
            ];
        }

        return $users;
    }

    public function createUser(array $data): bool {
        $role     = $data['role'] ?? 'Student';
        $name     = trim($data['name'] ?? '');
        $email    = trim($data['email'] ?? '');
        $program  = trim($data['program'] ?? '');
        $password = !empty($data['password'])
            ? password_hash($data['password'], PASSWORD_BCRYPT)
            : password_hash('password123', PASSWORD_BCRYPT);

        // Offline storage
        if (!isset($_SESSION['custom_users'])) {
            $_SESSION['custom_users'] = [];
        }

        if ($role !== 'Student') {
            $username = explode('@', $email)[0] ?: strtolower($role) . '_' . time();
            $customId = 'admin-' . str_pad((string)(count($_SESSION['custom_users']) + 10), 4, '0', STR_PAD_LEFT);

            $_SESSION['custom_users'][] = [
                'id'      => $customId,
                'name'    => $name ?: ucfirst($username),
                'role'    => $role,
                'program' => $program ?: ($role . ' Office'),
                'status'  => 'Active',
                'email'   => $email,
            ];

            if (!isset($_SESSION['custom_admins'])) {
                $_SESSION['custom_admins'] = [];
            }
            $_SESSION['custom_admins'][] = [
                'AdminID'        => 99 + count($_SESSION['custom_users']),
                'Username'       => $username,
                'PasswordHash'   => $password,
                'Role'           => $role,
                'DepartmentName' => $program ?: ($role . ' Office'),
            ];

            if (!$this->db) {
                return true;
            }

            $deptId = match($role) {
                'Registrar'       => 2,
                'Cashier'         => 3,
                'DepartmentStaff' => 1,
                default           => 2,
            };

            try {
                $stmt = $this->db->prepare("INSERT INTO AdminUser (Username, PasswordHash, Role, DepartmentID) VALUES (:u, :p, :role, :did)");
                return $stmt->execute([':u' => $username, ':p' => $password, ':role' => $role, ':did' => $deptId]);
            } catch (\Throwable $e) {
                return false;
            }
        } else {
            $parts = explode(' ', $name, 2);
            $first = $parts[0] ?: 'Student';
            $last  = $parts[1] ?? 'User';
            $customId = '2026-' . str_pad((string)(count($_SESSION['custom_users']) + 500), 5, '0', STR_PAD_LEFT);

            $_SESSION['custom_users'][] = [
                'id'      => $customId,
                'name'    => $name,
                'role'    => 'Student',
                'program' => $program ?: 'BS Information Technology',
                'status'  => 'Active',
                'email'   => $email,
            ];

            if (!isset($_SESSION['custom_students'])) {
                $_SESSION['custom_students'] = [];
            }
            $_SESSION['custom_students'][] = [
                'StudentID'    => 100 + count($_SESSION['custom_users']),
                'FirstName'    => $first,
                'LastName'     => $last,
                'Email'        => $email,
                'PasswordHash' => $password,
                'Program'      => $program ?: 'BS Information Technology',
                'YearLevel'    => 1,
                'Status'       => 'Active',
            ];

            if (!$this->db) {
                return true;
            }

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO Student (FirstName, LastName, Email, PasswordHash, Program, YearLevel, Status)
                    VALUES (:fn, :ln, :em, :pw, :prog, 1, 'Active')
                ");
                return $stmt->execute([
                    ':fn'   => $first,
                    ':ln'   => $last,
                    ':em'   => $email,
                    ':pw'   => $password,
                    ':prog' => $program,
                ]);
            } catch (\Throwable $e) {
                return false;
            }
        }
    }
}
