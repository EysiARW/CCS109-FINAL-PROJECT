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
        if ($this->grade === null || trim($this->grade) === '') {
            return 'In progress';
        }
        $upper = strtoupper(trim($this->grade));
        return match ($upper) {
            'INC'   => 'Incomplete',
            'DRP'   => 'Dropped',
            'W'     => 'Withdrawn',
            'IP'    => 'In progress',
            'NG'    => 'No Grade',
            default => is_numeric($this->grade)
                ? ((float)$this->grade <= 3.0 ? 'Passed' : ((float)$this->grade === 4.0 ? 'Conditional' : 'Failed'))
                : 'In progress',
        };
    }

    public function getStatusTone(): string {
        return match ($this->getRemarks()) {
            'Passed'                         => 'success',
            'Failed', 'Dropped', 'Withdrawn' => 'danger',
            default                          => 'warning',
        };
    }
}
