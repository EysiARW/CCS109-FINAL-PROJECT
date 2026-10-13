<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Student;
use PDO;

class EnrollmentRepository {
    private ?PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * @return array<int, array{enrollment_id: int, student_id: int, student_code: string, student_name: string, student_program: string, course_id: int, course_code: string, course_name: string, units: int, school_year: string, semester: string, grade: ?string}>
     */
    public function getAllEnrollments(?string $schoolYear = null, ?string $semester = null): array {
        if (!$this->db) {
            return $this->getDefaultEnrollments();
        }

        $sql = "
            SELECT e.EnrollmentID, e.StudentID, e.CourseID, e.SchoolYear, e.Semester, e.Grade,
                   s.FirstName, s.LastName, s.Program,
                   c.CourseCode, c.CourseName, c.Units
            FROM Enrollment e
            JOIN Student s ON e.StudentID = s.StudentID
            JOIN Course c ON e.CourseID = c.CourseID
        ";
        $params = [];
        $conditions = [];

        if ($schoolYear) {
            $conditions[] = "e.SchoolYear = :sy";
            $params[':sy'] = $schoolYear;
        }
        if ($semester) {
            $conditions[] = "e.Semester = :sem";
            $params[':sem'] = $semester;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY e.SchoolYear DESC, e.Semester ASC, s.LastName ASC, c.CourseCode ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'enrollment_id'   => (int)$r['EnrollmentID'],
                'student_id'      => (int)$r['StudentID'],
                'student_code'    => '2021-' . str_pad((string)$r['StudentID'], 5, '0', STR_PAD_LEFT),
                'student_name'    => $r['FirstName'] . ' ' . $r['LastName'],
                'student_program' => $r['Program'] ?: 'General Program',
                'course_id'       => (int)$r['CourseID'],
                'course_code'     => $r['CourseCode'],
                'course_name'     => $r['CourseName'],
                'units'           => (int)$r['Units'],
                'school_year'     => $r['SchoolYear'],
                'semester'        => $r['Semester'],
                'grade'           => $r['Grade'],
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array{id: int, code: string, name: string, units: int}>
     */
    public function getAllCourses(): array {
        if (!$this->db) {
            return [
                ['id' => 1, 'code' => 'IT 311', 'name' => 'Web Systems and Technologies', 'units' => 3],
                ['id' => 2, 'code' => 'IT 312', 'name' => 'Database Management Systems', 'units' => 3],
                ['id' => 3, 'code' => 'IT 313', 'name' => 'Information Assurance', 'units' => 3],
                ['id' => 4, 'code' => 'IT 314', 'name' => 'Systems Analysis and Design', 'units' => 3],
                ['id' => 5, 'code' => 'IT 221', 'name' => 'Object-Oriented Programming', 'units' => 3],
                ['id' => 6, 'code' => 'IT 222', 'name' => 'Data Structures and Algorithms', 'units' => 3],
            ];
        }

        $stmt = $this->db->query("SELECT CourseID, CourseCode, CourseName, Units FROM Course ORDER BY CourseCode ASC");
        $courses = [];
        while ($r = $stmt->fetch()) {
            $courses[] = [
                'id'    => (int)$r['CourseID'],
                'code'  => $r['CourseCode'],
                'name'  => $r['CourseName'],
                'units' => (int)$r['Units'],
            ];
        }
        return $courses;
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

    public function enrollStudent(int $studentId, int $courseId, string $schoolYear, string $semester): bool {
        if (!$this->db) {
            return true;
        }

        try {
            // Check if already enrolled
            $check = $this->db->prepare("
                SELECT COUNT(*) FROM Enrollment 
                WHERE StudentID = :sid AND CourseID = :cid AND SchoolYear = :sy AND Semester = :sem
            ");
            $check->execute([
                ':sid' => $studentId,
                ':cid' => $courseId,
                ':sy'  => $schoolYear,
                ':sem' => $semester,
            ]);
            if ((int)$check->fetchColumn() > 0) {
                return false;
            }

            $stmt = $this->db->prepare("
                INSERT INTO Enrollment (StudentID, CourseID, SchoolYear, Semester, Grade)
                VALUES (:sid, :cid, :sy, :sem, NULL)
            ");
            return $stmt->execute([
                ':sid' => $studentId,
                ':cid' => $courseId,
                ':sy'  => $schoolYear,
                ':sem' => $semester,
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function dropEnrollment(int $enrollmentId): bool {
        if (!$this->db) {
            return true;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM Enrollment WHERE EnrollmentID = :id");
            return $stmt->execute([':id' => $enrollmentId]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function updateGrade(int $enrollmentId, ?string $grade): bool {
        if (!$this->db) {
            return true;
        }

        try {
            $stmt = $this->db->prepare("UPDATE Enrollment SET Grade = :grade WHERE EnrollmentID = :id");
            return $stmt->execute([
                ':grade' => $grade !== '' ? $grade : null,
                ':id'    => $enrollmentId,
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function getDefaultEnrollments(): array {
        return [
            ['enrollment_id' => 1, 'student_id' => 1, 'student_code' => '2021-00001', 'student_name' => 'Juan Dela Cruz', 'student_program' => 'BS Information Technology', 'course_id' => 1, 'course_code' => 'IT 311', 'course_name' => 'Web Systems and Technologies', 'units' => 3, 'school_year' => '2025-2026', 'semester' => '1st', 'grade' => '1.25'],
            ['enrollment_id' => 2, 'student_id' => 1, 'student_code' => '2021-00001', 'student_name' => 'Juan Dela Cruz', 'student_program' => 'BS Information Technology', 'course_id' => 2, 'course_code' => 'IT 312', 'course_name' => 'Database Management Systems', 'units' => 3, 'school_year' => '2025-2026', 'semester' => '1st', 'grade' => '1.50'],
            ['enrollment_id' => 3, 'student_id' => 1, 'student_code' => '2021-00001', 'student_name' => 'Juan Dela Cruz', 'student_program' => 'BS Information Technology', 'course_id' => 3, 'course_code' => 'IT 313', 'course_name' => 'Information Assurance', 'units' => 3, 'school_year' => '2025-2026', 'semester' => '1st', 'grade' => '1.75'],
            ['enrollment_id' => 4, 'student_id' => 1, 'student_code' => '2021-00001', 'student_name' => 'Juan Dela Cruz', 'student_program' => 'BS Information Technology', 'course_id' => 4, 'course_code' => 'IT 314', 'course_name' => 'Systems Analysis and Design', 'units' => 3, 'school_year' => '2025-2026', 'semester' => '1st', 'grade' => null],
        ];
    }
}
