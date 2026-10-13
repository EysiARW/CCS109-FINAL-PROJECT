<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\Course;
use App\Models\Enrollment;
use PDO;

class CourseRepository {
    private ?PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * @return Course[]
     */
    public function getEnrolledCourses(int $studentId, string $schoolYear = '2025-2026', string $semester = '1st'): array {
        if (!$this->db) {
            return $this->getDefaultCourses();
        }

        $stmt = $this->db->prepare("
            SELECT c.CourseID, c.CourseCode, c.CourseName, c.Units, d.DepartmentID, d.DepartmentName, d.HeadName
            FROM Enrollment e
            JOIN Course c ON e.CourseID = c.CourseID
            JOIN Department d ON c.DepartmentID = d.DepartmentID
            WHERE e.StudentID = :sid AND e.SchoolYear = :sy
            ORDER BY c.CourseCode ASC
        ");
        $stmt->execute([':sid' => $studentId, ':sy' => $schoolYear]);
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            // If student 1 has no DB entries yet, return default courses
            return ($studentId === 1 || $studentId === 0) ? $this->getDefaultCourses() : [];
        }

        $schedules = [
            'IT 311' => ['MWF · 9:00–10:00 AM',  'ICT 204', 'M. Santos'],
            'IT 312' => ['TTh · 10:30–12:00 PM', 'ICT 206', 'J. Dela Cruz'],
            'IT 313' => ['MWF · 1:00–2:00 PM',   'ICT 201', 'A. Reyes'],
            'IT 314' => ['TTh · 2:00–3:30 PM',   'ICT 203', 'R. Mendoza'],
        ];

        $courses = [];
        foreach ($rows as $r) {
            $meta = $schedules[$r['CourseCode']] ?? ['MWF · 8:00–9:00 AM', 'ICT 101', $r['HeadName'] ?: 'Faculty'];
            $courses[] = new Course([
                'CourseID'       => $r['CourseID'],
                'CourseCode'     => $r['CourseCode'],
                'CourseName'     => $r['CourseName'],
                'Units'          => $r['Units'],
                'DepartmentID'   => $r['DepartmentID'],
                'DepartmentName' => $r['DepartmentName'],
                'Schedule'       => $meta[0],
                'Room'           => $meta[1],
                'Instructor'     => $meta[2],
            ]);
        }
        return $courses;
    }

    /**
     * @return array<string, array{term: string, date: string, gpa: string, enrollments: Enrollment[]}>
     */
    public function getStudentGrades(int $studentId): array {
        if (!$this->db) {
            return $this->getDefaultGrades();
        }

        $stmt = $this->db->prepare("
            SELECT e.EnrollmentID, e.StudentID, e.CourseID, e.SchoolYear, e.Semester, e.Grade,
                   c.CourseCode, c.CourseName, c.Units
            FROM Enrollment e
            JOIN Course c ON e.CourseID = c.CourseID
            WHERE e.StudentID = :sid
            ORDER BY e.SchoolYear DESC, e.Semester ASC, c.CourseCode ASC
        ");
        $stmt->execute([':sid' => $studentId]);
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            return ($studentId === 1 || $studentId === 0) ? $this->getDefaultGrades() : [
                'sem1' => [
                    'term'        => 'First semester · 2025–2026',
                    'date'        => 'Not yet available',
                    'gpa'         => '—',
                    'enrollments' => [],
                ],
                'sem2' => [
                    'term'        => 'Second semester · 2024–2025',
                    'date'        => 'Not yet available',
                    'gpa'         => '—',
                    'enrollments' => [],
                ]
            ];
        }

        $sem1 = [];
        $sem2 = [];

        foreach ($rows as $r) {
            $course = new Course([
                'CourseID'   => $r['CourseID'],
                'CourseCode' => $r['CourseCode'],
                'CourseName' => $r['CourseName'],
                'Units'      => $r['Units'],
            ]);
            $enrollment = new Enrollment($r, $course);

            if ($r['SchoolYear'] === '2025-2026' && $r['Semester'] === '1st') {
                $sem1[] = $enrollment;
            } else {
                $sem2[] = $enrollment;
            }
        }

        $gpa1 = $this->calculateGpa($sem1);
        $gpa2 = $this->calculateGpa($sem2);

        return [
            'sem1' => [
                'term'        => 'First semester · 2025–2026',
                'date'        => 'Released February 14, 2026',
                'gpa'         => $gpa1 !== null ? $gpa1 : '1.50',
                'enrollments' => $sem1,
            ],
            'sem2' => [
                'term'        => 'Second semester · 2024–2025',
                'date'        => 'Released July 28, 2025',
                'gpa'         => $gpa2 !== null ? $gpa2 : '1.80',
                'enrollments' => $sem2,
            ]
        ];
    }

    /**
     * @param Enrollment[] $enrollments
     */
    public function calculateGpa(array $enrollments): ?string {
        $totalPoints = 0.0;
        $gradedUnits = 0;

        foreach ($enrollments as $e) {
            if ($e->grade !== null && $e->grade !== '' && is_numeric($e->grade)) {
                $units = $e->course->units ?? 3;
                $totalPoints += (float)$e->grade * $units;
                $gradedUnits += $units;
            }
        }

        return $gradedUnits > 0 ? number_format($totalPoints / $gradedUnits, 2) : null;
    }

    /**
     * @return Course[]
     */
    public function getDefaultCourses(): array {
        return [
            new Course(['CourseCode' => 'IT 311', 'CourseName' => 'Web Systems and Technologies', 'Units' => 3, 'Schedule' => 'MWF · 9:00–10:00 AM',  'Room' => 'ICT 204', 'Instructor' => 'M. Santos']),
            new Course(['CourseCode' => 'IT 312', 'CourseName' => 'Database Management Systems',  'Units' => 3, 'Schedule' => 'TTh · 10:30–12:00 PM', 'Room' => 'ICT 206', 'Instructor' => 'J. Dela Cruz']),
            new Course(['CourseCode' => 'IT 313', 'CourseName' => 'Information Assurance',        'Units' => 3, 'Schedule' => 'MWF · 1:00–2:00 PM',   'Room' => 'ICT 201', 'Instructor' => 'A. Reyes']),
            new Course(['CourseCode' => 'IT 314', 'CourseName' => 'Systems Analysis and Design',  'Units' => 3, 'Schedule' => 'TTh · 2:00–3:30 PM',   'Room' => 'ICT 203', 'Instructor' => 'R. Mendoza']),
        ];
    }

    /**
     * @return array<string, array{term: string, date: string, gpa: string, enrollments: Enrollment[]}>
     */
    public function getDefaultGrades(): array {
        $sem1Courses = [
            new Enrollment(['Grade' => '1.25'], new Course(['CourseCode' => 'IT 311', 'CourseName' => 'Web Systems and Technologies', 'Units' => 3])),
            new Enrollment(['Grade' => '1.50'], new Course(['CourseCode' => 'IT 312', 'CourseName' => 'Database Management Systems',  'Units' => 3])),
            new Enrollment(['Grade' => '1.75'], new Course(['CourseCode' => 'IT 313', 'CourseName' => 'Information Assurance',        'Units' => 3])),
            new Enrollment(['Grade' => null],   new Course(['CourseCode' => 'IT 314', 'CourseName' => 'Systems Analysis and Design',  'Units' => 3])),
        ];

        $sem2Courses = [
            new Enrollment(['Grade' => '1.75'], new Course(['CourseCode' => 'IT 221', 'CourseName' => 'Object-Oriented Programming', 'Units' => 3])),
            new Enrollment(['Grade' => '2.00'], new Course(['CourseCode' => 'IT 222', 'CourseName' => 'Data Structures and Algorithms', 'Units' => 3])),
        ];

        return [
            'sem1' => [
                'term'        => 'First semester · 2025–2026',
                'date'        => 'Released February 14, 2026',
                'gpa'         => $this->calculateGpa($sem1Courses) ?? '1.50',
                'enrollments' => $sem1Courses,
            ],
            'sem2' => [
                'term'        => 'Second semester · 2024–2025',
                'date'        => 'Released July 28, 2025',
                'gpa'         => $this->calculateGpa($sem2Courses) ?? '1.88',
                'enrollments' => $sem2Courses,
            ]
        ];
    }
}
