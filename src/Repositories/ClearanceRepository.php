<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\Clearance;
use PDO;

class ClearanceRepository {
    private ?PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * @return Clearance[]
     */
    public function getStudentClearances(int $studentId): array {
        if (!$this->db) {
            return $this->getDefaultClearances();
        }

        $stmt = $this->db->prepare("
            SELECT c.*, d.DepartmentName 
            FROM Clearance c
            JOIN Department d ON c.DepartmentID = d.DepartmentID
            WHERE c.StudentID = :sid
        ");
        $stmt->execute([':sid' => $studentId]);
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            return ($studentId === 1 || $studentId === 0) ? $this->getDefaultClearances() : [];
        }

        return array_map(fn($r) => new Clearance($r), $rows);
    }

    /**
     * @return array<int, array{clearance_id: int, student_id: int, student_code: string, student_name: string, student_program: string, department_id: int, department_name: string, status: string, date_cleared: ?string}>
     */
    public function getAllClearances(?int $departmentId = null): array {
        if (!$this->db) {
            return $this->getDefaultAllClearances();
        }

        $sql = "
            SELECT c.ClearanceID, c.StudentID, c.DepartmentID, c.Status, c.DateCleared,
                   s.FirstName, s.LastName, s.Program,
                   d.DepartmentName
            FROM Clearance c
            JOIN Student s ON c.StudentID = s.StudentID
            JOIN Department d ON c.DepartmentID = d.DepartmentID
        ";
        $params = [];
        if ($departmentId) {
            $sql .= " WHERE c.DepartmentID = :did";
            $params[':did'] = $departmentId;
        }
        $sql .= " ORDER BY c.Status ASC, d.DepartmentName ASC, s.LastName ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'clearance_id'    => (int)$r['ClearanceID'],
                'student_id'      => (int)$r['StudentID'],
                'student_code'    => '2021-' . str_pad((string)$r['StudentID'], 5, '0', STR_PAD_LEFT),
                'student_name'    => $r['FirstName'] . ' ' . $r['LastName'],
                'student_program' => $r['Program'] ?: 'General Program',
                'department_id'   => (int)$r['DepartmentID'],
                'department_name' => $r['DepartmentName'],
                'status'          => $r['Status'],
                'date_cleared'    => $r['DateCleared'],
            ];
        }
        return $result;
    }

    public function updateClearanceStatus(int $clearanceId, string $status): bool {
        if (!$this->db) {
            return true;
        }

        $dateCleared = ($status === 'Cleared') ? date('Y-m-d') : null;
        try {
            $stmt = $this->db->prepare("
                UPDATE Clearance 
                SET Status = :st, DateCleared = :dc 
                WHERE ClearanceID = :id
            ");
            return $stmt->execute([
                ':st' => $status,
                ':dc' => $dateCleared,
                ':id' => $clearanceId,
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function getAllDepartments(): array {
        if (!$this->db) {
            return [
                ['id' => 1, 'name' => 'College of Computing Studies'],
                ['id' => 2, 'name' => "Registrar's Office"],
                ['id' => 3, 'name' => "Cashier's Office"],
                ['id' => 4, 'name' => 'University Library'],
            ];
        }

        $stmt = $this->db->query("SELECT DepartmentID, DepartmentName FROM Department ORDER BY DepartmentName ASC");
        $deps = [];
        while ($r = $stmt->fetch()) {
            $deps[] = [
                'id'   => (int)$r['DepartmentID'],
                'name' => $r['DepartmentName'],
            ];
        }
        return $deps;
    }

    /**
     * @return array<int, array{id: int, code: string, name: string, program: string}>
     */
    public function getAllStudents(): array {
        if (!$this->db) {
            return [
                ['id' => 1, 'code' => '2021-00001', 'name' => 'Juan Dela Cruz', 'program' => 'BS Information Technology'],
                ['id' => 2, 'code' => '2021-00002', 'name' => 'Maria Santos', 'program' => 'BS Accountancy'],
                ['id' => 3, 'code' => '2021-00003', 'name' => 'Paolo Gomez', 'program' => 'BS Information Technology'],
            ];
        }

        $stmt = $this->db->query("SELECT StudentID, FirstName, LastName, Program FROM Student WHERE Status = 'Active' ORDER BY LastName ASC");
        $students = [];
        while ($r = $stmt->fetch()) {
            $students[] = [
                'id'      => (int)$r['StudentID'],
                'code'    => '2021-' . str_pad((string)$r['StudentID'], 5, '0', STR_PAD_LEFT),
                'name'    => $r['FirstName'] . ' ' . $r['LastName'],
                'program' => $r['Program'] ?: 'General Program',
            ];
        }
        return $students;
    }

    public function addClearance(int $studentId, int $departmentId, string $status = 'Pending'): bool {
        if (!$this->db) {
            return true;
        }

        $dateCleared = ($status === 'Cleared') ? date('Y-m-d') : null;
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Clearance (StudentID, DepartmentID, Status, DateCleared)
                VALUES (:sid, :did, :st, :dc)
                ON DUPLICATE KEY UPDATE Status = VALUES(Status), DateCleared = VALUES(DateCleared)
            ");
            return $stmt->execute([
                ':sid' => $studentId,
                ':did' => $departmentId,
                ':st'  => $status,
                ':dc'  => $dateCleared,
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @return Clearance[]
     */
    public function getDefaultClearances(): array {
        return [
            new Clearance(['DepartmentName' => 'University Library',        'Status' => 'Cleared']),
            new Clearance(['DepartmentName' => "Cashier's Office",         'Status' => 'Pending']),
            new Clearance(['DepartmentName' => 'College of Computing Studies', 'Status' => 'Cleared']),
        ];
    }

    private function getDefaultAllClearances(): array {
        return [
            ['clearance_id' => 1, 'student_id' => 1, 'student_code' => '2021-00001', 'student_name' => 'Juan Dela Cruz', 'student_program' => 'BS Information Technology', 'department_id' => 4, 'department_name' => 'University Library', 'status' => 'Cleared', 'date_cleared' => '2025-10-01'],
            ['clearance_id' => 2, 'student_id' => 1, 'student_code' => '2021-00001', 'student_name' => 'Juan Dela Cruz', 'student_program' => 'BS Information Technology', 'department_id' => 3, 'department_name' => "Cashier's Office", 'status' => 'Pending', 'date_cleared' => null],
            ['clearance_id' => 3, 'student_id' => 1, 'student_code' => '2021-00001', 'student_name' => 'Juan Dela Cruz', 'student_program' => 'BS Information Technology', 'department_id' => 1, 'department_name' => 'College of Computing Studies', 'status' => 'Cleared', 'date_cleared' => '2025-09-28'],
        ];
    }
}
