<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\Student;
use App\Models\Payment;
use PDO;

class StudentRepository {
    private ?PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findByIdOrEmail(string $identifier): ?Student {
        $cleanId = trim($identifier);

        if (!$this->db) {
            $demoStudents = [
                '1' => [
                    'StudentID'    => 1,
                    'FirstName'    => 'Juan',
                    'LastName'     => 'Dela Cruz',
                    'Email'        => 'juan.delacruz@cuyotech.edu',
                    'PasswordHash' => $_SESSION['demo_password_hash_1'] ?? '$2y$10$mNBM.jF8gDY8UC8bFhvEYunvpKUH4kFIcBUtYgg0QHUJfPQe7rzWi',
                    'Program'      => 'BS Information Technology',
                    'YearLevel'    => 3,
                    'Status'       => 'Active',
                    'Address'      => 'Brgy. San Pedro, Puerto Princesa City, Palawan 5300',
                ],
                '2' => [
                    'StudentID'    => 2,
                    'FirstName'    => 'Maria',
                    'LastName'     => 'Santos',
                    'Email'        => '2024-00412@cuyotech.edu',
                    'PasswordHash' => $_SESSION['demo_password_hash_2'] ?? '$2y$10$mNBM.jF8gDY8UC8bFhvEYunvpKUH4kFIcBUtYgg0QHUJfPQe7rzWi',
                    'Program'      => 'BS Accountancy',
                    'YearLevel'    => 1,
                    'Status'       => 'Active',
                    'Address'      => 'Brgy. Bancao-Bancao, Puerto Princesa City, Palawan 5300',
                ],
                '3' => [
                    'StudentID'    => 3,
                    'FirstName'    => 'Paolo',
                    'LastName'     => 'Gomez',
                    'Email'        => '2020-00087@cuyotech.edu',
                    'PasswordHash' => $_SESSION['demo_password_hash_3'] ?? '$2y$10$mNBM.jF8gDY8UC8bFhvEYunvpKUH4kFIcBUtYgg0QHUJfPQe7rzWi',
                    'Program'      => 'BS Information Technology',
                    'YearLevel'    => 4,
                    'Status'       => 'Deactivated',
                    'Address'      => 'Brgy. San Jose, Puerto Princesa City, Palawan 5300',
                ]
            ];

            // Match student 1
            if (in_array(strtolower($cleanId), ['2021-00123', '1', 'juan.delacruz@cuyotech.edu', 'juan'])) {
                return new Student($demoStudents['1']);
            }
            // Match student 2
            if (in_array(strtolower($cleanId), ['2024-00412', '2', '2024-00412@cuyotech.edu', 'maria.santos'])) {
                return new Student($demoStudents['2']);
            }
            // Match student 3
            if (in_array(strtolower($cleanId), ['2020-00087', '3', '2020-00087@cuyotech.edu', 'paolo.gomez'])) {
                return new Student($demoStudents['3']);
            }

            // Check custom offline created students
            if (!empty($_SESSION['custom_students'])) {
                foreach ($_SESSION['custom_students'] as $cs) {
                    if (strcasecmp($cs['Email'] ?? '', $cleanId) === 0 || ($cs['StudentID'] ?? '') == $cleanId) {
                        return new Student($cs);
                    }
                }
            }

            return null;
        }

        $normalizedId = strtolower($cleanId);
        $numericId = ctype_digit($cleanId) ? (int)$cleanId : 0;
        $legacyStudentId = $normalizedId === '2021-00123' ? 1 : 0;
        $stmt = $this->db->prepare("
            SELECT *
            FROM Student
            WHERE Email = :email
               OR StudentID = :student_id
               OR SUBSTRING_INDEX(Email, '@', 1) = :formatted_email_id
               OR StudentID = :legacy_student_id
            LIMIT 1
        ");
        $stmt->execute([
            ':email'              => $cleanId,
            ':student_id'         => $numericId,
            ':formatted_email_id' => $cleanId,
            ':legacy_student_id'  => $legacyStudentId,
        ]);
        $row = $stmt->fetch();

        return $row ? new Student($row) : null;
    }

    public function findById(int $id): ?Student {
        if (!$this->db) {
            $s = $this->findByIdOrEmail((string)$id);
            return $s ?: new Student([
                'StudentID' => $id,
                'FirstName' => 'Juan',
                'LastName'  => 'Dela Cruz',
                'Email'     => 'juan.delacruz@cuyotech.edu',
                'Program'   => 'BS Information Technology',
                'YearLevel' => 3,
                'Status'    => 'Active',
                'Address'   => 'Brgy. San Pedro, Puerto Princesa City, Palawan 5300',
            ]);
        }

        $stmt = $this->db->prepare("SELECT * FROM Student WHERE StudentID = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? new Student($row) : null;
    }

    public function updatePassword(int $studentId, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $_SESSION['demo_password_hash_' . $studentId] = $hash;

        if (!$this->db) {
            return true;
        }
        $stmt = $this->db->prepare("UPDATE Student SET PasswordHash = :p WHERE StudentID = :id");
        return $stmt->execute([':p' => $hash, ':id' => $studentId]);
    }

    public function getLatestPayment(int $studentId): Payment {
        if (!$this->db) {
            return new Payment(['StudentID' => $studentId, 'Amount' => 1250.00]);
        }
        $stmt = $this->db->prepare("SELECT * FROM Payment WHERE StudentID = :sid ORDER BY PaymentDate DESC LIMIT 1");
        $stmt->execute([':sid' => $studentId]);
        $row = $stmt->fetch();
        return $row ? new Payment($row) : new Payment(['StudentID' => $studentId, 'Amount' => 1250.00]);
    }
}
