<?php
namespace App\Controllers;

use App\Repositories\CourseRepository;
use App\Utils\View;

class AcademicController {
    private CourseRepository $courseRepo;

    public function __construct() {
        $this->courseRepo = new CourseRepository();
    }

    public function subjects(): void {
        $studentId = (int)($_SESSION['student_id'] ?? 1);
        $courses   = $this->courseRepo->getEnrolledCourses($studentId);

        View::render('student/subjects', [
            'page'    => 'subjects',
            'courses' => $courses,
        ]);
    }

    public function grades(): void {
        $studentId = (int)($_SESSION['student_id'] ?? 1);
        $grades    = $this->courseRepo->getStudentGrades($studentId);

        View::render('student/grades', [
            'page'   => 'grades',
            'grades' => $grades,
        ]);
    }
}
