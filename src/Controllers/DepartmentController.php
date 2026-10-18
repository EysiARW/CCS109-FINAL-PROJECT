<?php
namespace App\Controllers;

use App\Repositories\ClearanceRepository;
use App\Utils\View;

class DepartmentController {
    private ClearanceRepository $clearanceRepo;

    public function __construct() {
        $this->clearanceRepo = new ClearanceRepository();
    }

    public function clearance(): void {
        $deptFilter  = !empty($_GET['dept_id']) ? (int)$_GET['dept_id'] : null;
        $clearances  = $this->clearanceRepo->getAllClearances($deptFilter);
        $departments = $this->clearanceRepo->getAllDepartments();
        $students    = $this->clearanceRepo->getAllStudents();

        $updated = !empty($_GET['status_updated']);
        $added   = !empty($_GET['clearance_added']);

        View::render('department/clearance', [
            'page'          => 'clearance',
            'clearances'    => $clearances,
            'departments'   => $departments,
            'students'      => $students,
            'deptFilter'    => $deptFilter,
            'statusUpdated' => $updated,
            'clearanceAdded'=> $added,
        ]);
    }

    public function updateClearance(): void {
        $clearanceId = (int)($_POST['clearance_id'] ?? 0);
        $status      = trim($_POST['status'] ?? 'Cleared');

        if ($clearanceId > 0 && in_array($status, ['Cleared', 'Pending', 'Hold'], true)) {
            $this->clearanceRepo->updateClearanceStatus($clearanceId, $status);
        }

        header('Location: ?page=clearance&status_updated=1');
        exit;
    }

    public function addClearance(): void {
        $studentId    = (int)($_POST['student_id'] ?? 0);
        $studentInput = trim((string)($_POST['student_search'] ?? $_POST['student_input'] ?? ''));

        if ($studentId <= 0 && $studentInput !== '') {
            $found = $this->clearanceRepo->findStudentByNameOrCode($studentInput);
            if ($found) {
                $studentId = $found['id'];
            }
        }

        $departmentId = (int)($_POST['department_id'] ?? 0);
        $status       = trim($_POST['status'] ?? 'Pending');

        if ($studentId > 0 && $departmentId > 0) {
            $this->clearanceRepo->addClearance($studentId, $departmentId, $status);
        }

        header('Location: ?page=clearance&clearance_added=1');
        exit;
    }
}
