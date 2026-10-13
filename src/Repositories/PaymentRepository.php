<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\Payment;
use PDO;

class PaymentRepository {
    private ?PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * @return array<int, array{payment_id: int, student_id: int, student_code: string, student_name: string, student_program: string, amount: float, payment_date: string, or_number: string, payment_type: string}>
     */
    public function getAllPayments(): array {
        if (!$this->db) {
            return $this->getDefaultPayments();
        }

        $stmt = $this->db->query("
            SELECT p.PaymentID, p.StudentID, p.Amount, p.PaymentDate, p.ORNumber, p.PaymentType,
                   s.FirstName, s.LastName, s.Program
            FROM Payment p
            JOIN Student s ON p.StudentID = s.StudentID
            ORDER BY p.PaymentDate DESC, p.PaymentID DESC
        ");
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'payment_id'      => (int)$r['PaymentID'],
                'student_id'      => (int)$r['StudentID'],
                'student_code'    => '2021-' . str_pad((string)$r['StudentID'], 5, '0', STR_PAD_LEFT),
                'student_name'    => $r['FirstName'] . ' ' . $r['LastName'],
                'student_program' => $r['Program'] ?: 'General Program',
                'amount'          => (float)$r['Amount'],
                'payment_date'    => $r['PaymentDate'],
                'or_number'       => $r['ORNumber'],
                'payment_type'    => $r['PaymentType'],
            ];
        }
        return $result;
    }

    /**
     * Record a new payment
     */
    public function recordPayment(int $studentId, float $amount, string $paymentType, ?string $orNumber = null): bool {
        if (!$this->db) {
            return true;
        }

        if (empty($orNumber)) {
            $orNumber = 'OR-' . date('Y') . '-' . str_pad((string)rand(100, 9999), 4, '0', STR_PAD_LEFT);
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO Payment (StudentID, Amount, PaymentDate, ORNumber, PaymentType)
                VALUES (:sid, :amt, CURDATE(), :orn, :ptype)
            ");
            return $stmt->execute([
                ':sid'   => $studentId,
                ':amt'   => $amount,
                ':orn'   => $orNumber,
                ':ptype' => $paymentType,
            ]);
        } catch (\Throwable $e) {
            return false;
        }
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

        $stmt = $this->db->query("SELECT StudentID, FirstName, LastName, Program FROM Student ORDER BY LastName ASC");
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

    public function findStudentByNameOrCode(string $query): ?array {
        $q = trim($query);
        if ($q === '') return null;
        $students = $this->getAllStudents();
        $qLower = strtolower($q);
        foreach ($students as $st) {
            if (strtolower($st['code']) === $qLower || strtolower($st['name']) === $qLower) {
                return $st;
            }
        }
        foreach ($students as $st) {
            if (str_contains(strtolower($st['name']), $qLower) || str_contains(strtolower($st['code']), $qLower)) {
                return $st;
            }
        }
        $num = (int)preg_replace('/\D/', '', $q);
        if ($num > 0) {
            foreach ($students as $st) {
                if ($st['id'] === $num) {
                    return $st;
                }
            }
        }
        return null;
    }

    private function getDefaultPayments(): array {
        return [
            ['payment_id' => 1, 'student_id' => 1, 'student_code' => '2021-00001', 'student_name' => 'Juan Dela Cruz', 'student_program' => 'BS Information Technology', 'amount' => 1250.00, 'payment_date' => '2025-09-15', 'or_number' => 'OR-2025-0182', 'payment_type' => 'Tuition'],
            ['payment_id' => 2, 'student_id' => 2, 'student_code' => '2021-00002', 'student_name' => 'Maria Santos', 'student_program' => 'BS Accountancy', 'amount' => 500.00, 'payment_date' => '2025-09-20', 'or_number' => 'OR-2025-0183', 'payment_type' => 'Miscellaneous'],
        ];
    }
}
