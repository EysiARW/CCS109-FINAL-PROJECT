<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\DocumentRequest;
use PDO;

class RequestRepository {
    private ?PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
        if ($this->db) {
            $this->ensureCopiesColumn();
        }
    }

    private function ensureCopiesColumn(): void {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'DocumentRequest'
              AND COLUMN_NAME = 'Copies'
        ");
        if ((int)$stmt->fetchColumn() === 0) {
            $this->db->exec("ALTER TABLE DocumentRequest ADD COLUMN Copies INT NOT NULL DEFAULT 1");
        }
    }

    /**
     * @return DocumentRequest[]
     */
    public function getStudentRequests(int $studentId): array {
        $requests = [];

        if (!$this->db) {
            $base = [
                new DocumentRequest(['RequestID' => 48, 'StudentID' => 1, 'DocumentType' => 'Transcript of Records',        'Copies' => 1, 'Status' => 'Processing']),
                new DocumentRequest(['RequestID' => 31, 'StudentID' => 1, 'DocumentType' => 'Certification',                'Copies' => 2, 'Status' => 'Released']),
                new DocumentRequest(['RequestID' => 24, 'StudentID' => 1, 'DocumentType' => 'Certificate of Registration', 'Copies' => 1, 'Status' => 'Pending']),
            ];

            // Filter for student 1 demo
            if ($studentId === 1 || $studentId === 0) {
                $requests = $base;
            }

            // Merge session demo requests
            if (!empty($_SESSION['custom_requests'])) {
                foreach ($_SESSION['custom_requests'] as $cr) {
                    if ((int)$cr['StudentID'] === $studentId) {
                        array_unshift($requests, new DocumentRequest($cr));
                    }
                }
            }

            // Apply any status overrides
            if (!empty($_SESSION['request_status_overrides'])) {
                foreach ($requests as $r) {
                    if (isset($_SESSION['request_status_overrides'][$r->requestId])) {
                        $r->status = $_SESSION['request_status_overrides'][$r->requestId];
                    }
                }
            }

            return $requests;
        }

        $stmt = $this->db->prepare("SELECT * FROM DocumentRequest WHERE StudentID = :sid ORDER BY RequestID DESC");
        $stmt->execute([':sid' => $studentId]);
        $rows = $stmt->fetchAll();

        return array_map(fn($r) => new DocumentRequest($r), $rows);
    }

    public function getLatestRequest(int $studentId): ?DocumentRequest {
        $requests = $this->getStudentRequests($studentId);
        return $requests[0] ?? null;
    }

    public function create(int $studentId, string $docType, int $copies): int {
        $newId = rand(100, 999);

        // Always store in session so demo mode immediately reflects it
        if (!isset($_SESSION['custom_requests'])) {
            $_SESSION['custom_requests'] = [];
        }

        $studentName = $_SESSION['user_name'] ?? 'Juan Dela Cruz';

        $_SESSION['custom_requests'][] = [
            'RequestID'    => $newId,
            'StudentID'    => $studentId,
            'DocumentType' => $docType,
            'Copies'       => $copies,
            'RequestDate'  => date('Y-m-d'),
            'Status'       => 'Pending',
            'StudentName'  => $studentName,
        ];

        if (!$this->db) {
            return $newId;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO DocumentRequest (StudentID, DocumentType, Copies, RequestDate, Status)
                VALUES (:sid, :type, :copies, CURDATE(), 'Pending')
            ");
            $stmt->execute([':sid' => $studentId, ':type' => $docType, ':copies' => $copies]);
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            return $newId;
        }
    }

    /**
     * @return DocumentRequest[]
     */
    public function getAllRequests(): array {
        if (!$this->db) {
            $base = [
                new DocumentRequest(['RequestID' => 48, 'StudentID' => 1, 'DocumentType' => 'Transcript of Records',        'Copies' => 1, 'Status' => 'Processing',        'StudentName' => 'Juan Dela Cruz']),
                new DocumentRequest(['RequestID' => 47, 'StudentID' => 2, 'DocumentType' => 'Certification',                'Copies' => 2, 'Status' => 'Pending',           'StudentName' => 'Maria Santos']),
                new DocumentRequest(['RequestID' => 46, 'StudentID' => 3, 'DocumentType' => 'Certificate of Registration', 'Copies' => 1, 'Status' => 'Ready for release', 'StudentName' => 'Paolo Gomez']),
            ];

            if (!empty($_SESSION['custom_requests'])) {
                foreach ($_SESSION['custom_requests'] as $cr) {
                    array_unshift($base, new DocumentRequest($cr));
                }
            }

            if (!empty($_SESSION['request_status_overrides'])) {
                foreach ($base as $r) {
                    if (isset($_SESSION['request_status_overrides'][$r->requestId])) {
                        $r->status = $_SESSION['request_status_overrides'][$r->requestId];
                    }
                }
            }

            return $base;
        }

        $stmt = $this->db->query("
            SELECT r.*, CONCAT(s.FirstName, ' ', s.LastName) as StudentName
            FROM DocumentRequest r
            JOIN Student s ON r.StudentID = s.StudentID
            ORDER BY r.RequestID DESC
        ");
        $rows = $stmt->fetchAll();

        return array_map(fn($r) => new DocumentRequest($r), $rows);
    }

    public function updateStatus(int $requestId, string $action): bool {
        $statusMap = [
            'Confirm payment' => 'Processing',
            'Mark ready'      => 'Ready for release',
            'Mark released'   => 'Released',
            'Reject'          => 'Rejected',
            'Cancel'          => 'Cancelled',
        ];
        $newStatus = $statusMap[$action] ?? 'Processing';

        if (!isset($_SESSION['request_status_overrides'])) {
            $_SESSION['request_status_overrides'] = [];
        }
        $_SESSION['request_status_overrides'][$requestId] = $newStatus;

        if (!$this->db) {
            return true;
        }

        try {
            $stmt = $this->db->prepare("UPDATE DocumentRequest SET Status = :st WHERE RequestID = :id");
            return $stmt->execute([':st' => $newStatus, ':id' => $requestId]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function cancelRequest(int $requestId, int $studentId): bool {
        return $this->updateStatus($requestId, 'Cancel');
    }
}
