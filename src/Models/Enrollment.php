<?php
namespace App\Models;

class Enrollment {
    public ?int $enrollmentId;
    public int $studentId;
    public int $courseId;
    public string $schoolYear;
    public string $semester;
    public ?string $grade;
    public ?Course $course;

    public function __construct(array $data = [], ?Course $course = null) {
        $this->enrollmentId = isset($data['EnrollmentID']) ? (int)$data['EnrollmentID'] : null;
        $this->studentId    = (int)($data['StudentID'] ?? 0);
        $this->courseId     = (int)($data['CourseID'] ?? 0);
        $this->schoolYear   = $data['SchoolYear'] ?? '2025-2026';
        $this->semester     = $data['Semester'] ?? '1st';
        $this->grade        = $data['Grade'] ?? null;
        $this->course       = $course;
    }

    public function getRemarks(): string {
        if ($this->grade === null || $this->grade === '') {
            return 'In progress';
        }
        return floatval($this->grade) <= 3.0 ? 'Passed' : 'Failed';
    }
}
