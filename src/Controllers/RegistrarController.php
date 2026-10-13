<?php
namespace App\Controllers;

use App\Repositories\EnrollmentRepository;
use App\Utils\View;

class RegistrarController {
    private EnrollmentRepository $enrollmentRepo;

    public function __construct() {
        $this->enrollmentRepo = new EnrollmentRepository();
    }

    public function enrollment(): void {
        $schoolYear = $_GET['sy'] ?? null;
        $semester   = $_GET['sem'] ?? null;

        $enrollments = $this->enrollmentRepo->getAllEnrollments($schoolYear, $semester);
        $courses     = $this->enrollmentRepo->getAllCourses();
        $students    = $this->enrollmentRepo->getAllStudents();

        $enrolled = !empty($_GET['enrolled']);
        $dropped  = !empty($_GET['dropped']);
        $error    = $_GET['error'] ?? null;

        $errorMessage = match ($error) {
            'already_enrolled' => 'Student is already enrolled in this course for the selected term.',
            'missing_fields'   => 'Please select both a student and a course.',
            'failed'           => 'Could not process enrollment. Please try again.',
            default            => null,
        };

        View::render('registrar/enrollment', [
            'page'         => 'enrollment',
            'enrollments'  => $enrollments,
            'courses'      => $courses,
            'students'     => $students,
            'schoolYear'   => $schoolYear,
            'semester'     => $semester,
            'enrolled'     => $enrolled,
            'dropped'      => $dropped,
            'errorMessage' => $errorMessage,
        ]);
    }

    public function enrollStudent(): void {
        $studentId    = (int)($_POST['student_id'] ?? 0);
        $studentInput = trim((string)($_POST['student_search'] ?? $_POST['student_input'] ?? ''));

        if ($studentId <= 0 && $studentInput !== '') {
            $found = $this->enrollmentRepo->findStudentByNameOrCode($studentInput);
            if ($found) {
                $studentId = $found['id'];
            }
        }

        $courseId   = (int)($_POST['course_id'] ?? 0);
        $schoolYear = trim($_POST['school_year'] ?? '2025-2026');
        $semester   = trim($_POST['semester'] ?? '1st');

        if ($studentId <= 0 || $courseId <= 0) {
            header('Location: ?page=enrollment&error=missing_fields');
            exit;
        }

        $ok = $this->enrollmentRepo->enrollStudent($studentId, $courseId, $schoolYear, $semester);
        if ($ok) {
            header('Location: ?page=enrollment&enrolled=1');
        } else {
            header('Location: ?page=enrollment&error=already_enrolled');
        }
        exit;
    }

    public function dropEnrollment(): void {
        $enrollmentId = (int)($_POST['enrollment_id'] ?? 0);
        if ($enrollmentId > 0) {
            $this->enrollmentRepo->dropEnrollment($enrollmentId);
        }
        header('Location: ?page=enrollment&dropped=1');
        exit;
    }

    public function gradesEncoding(): void {
        $schoolYear = $_GET['sy'] ?? null;
        $semester   = $_GET['sem'] ?? null;

        $enrollments = $this->enrollmentRepo->getAllEnrollments($schoolYear, $semester);
        $courses     = $this->enrollmentRepo->getAllCourses();
        $updated     = !empty($_GET['grade_updated']);

        View::render('registrar/grades_encoding', [
            'page'         => 'grades-encoding',
            'enrollments'  => $enrollments,
            'courses'      => $courses,
            'schoolYear'   => $schoolYear,
            'semester'     => $semester,
            'gradeUpdated' => $updated,
        ]);
    }

    public function encodeGrade(): void {
        $enrollmentId = (int)($_POST['enrollment_id'] ?? 0);
        $grade        = trim($_POST['grade'] ?? '');

        if ($enrollmentId > 0) {
            $this->enrollmentRepo->updateGrade($enrollmentId, $grade !== '' ? $grade : null);
        }

        $redirectPage = $_POST['redirect_to'] ?? 'grades-encoding';
        header('Location: ?page=' . urlencode($redirectPage) . '&grade_updated=1');
        exit;
    }
}
