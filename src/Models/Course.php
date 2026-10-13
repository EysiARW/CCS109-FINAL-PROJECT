<?php
namespace App\Models;

class Course {
    public ?int $courseId;
    public string $courseCode;
    public string $courseName;
    public int $units;
    public int $departmentId;
    public ?string $departmentName;
    public ?string $instructor;
    public ?string $schedule;
    public ?string $room;

    public function __construct(array $data = []) {
        $this->courseId       = isset($data['CourseID']) ? (int)$data['CourseID'] : null;
        $this->courseCode     = $data['CourseCode'] ?? '';
        $this->courseName     = $data['CourseName'] ?? '';
        $this->units          = isset($data['Units']) ? (int)$data['Units'] : 3;
        $this->departmentId   = isset($data['DepartmentID']) ? (int)$data['DepartmentID'] : 1;
        $this->departmentName = $data['DepartmentName'] ?? '';
        $this->instructor     = $data['Instructor'] ?? 'Faculty';
        $this->schedule       = $data['Schedule'] ?? 'MWF · 9:00–10:00 AM';
        $this->room           = $data['Room'] ?? 'ICT 201';
    }
}
