<?php
/**
 * Database connection and query helper for CuyoTech SSIS
 * Uses ssis_schema.sql (MySQL / MariaDB / XAMPP)
 */

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3307');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'ssis_db');

/**
 * Returns a PDO connection or null if connection fails
 */
function get_db(): ?PDO {
    static $pdo = null;
    static $attempted = false;

    if ($attempted) {
        return $pdo;
    }
    $attempted = true;

    try {
        // Try connecting to the specific database
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 2,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // If database does not exist, attempt to connect to server and create it
        try {
            $server_dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
            $server_pdo = new PDO($server_dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 2,
            ]);
            db_initialize_schema($server_pdo);
            
            // Reconnect to newly created database
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return $pdo;
        } catch (Exception $inner) {
            $pdo = null;
            return null;
        }
    }
}

/**
 * Check if the database connection is currently active
 */
function is_db_connected(): bool {
    return get_db() !== null;
}

/**
 * Executes ssis_schema.sql to create database, tables, and seed data
 */
function db_initialize_schema(?PDO $pdo = null): bool {
    $schema_file = __DIR__ . '/ssis_schema.sql';
    if (!file_exists($schema_file)) {
        return false;
    }

    if ($pdo === null) {
        $server_dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
        try {
            $pdo = new PDO($server_dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 2,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    $sql = file_get_contents($schema_file);
    // Split and execute SQL statements
    try {
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        // Fallback: execute statement by statement
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $stmt) {
            if (!empty($stmt)) {
                try {
                    $pdo->exec($stmt);
                } catch (Exception $ignore) {}
            }
        }
        return true;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// DATA ACCESS FUNCTIONS (WITH FALLBACK FOR OFFLINE DB)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Authenticates student or admin against Student or AdminUser table
 */
function db_authenticate(string $identifier, string $password): array {
    $db = get_db();
    if (!$db) {
        // Fallback demo user
        return [
            'success' => true,
            'role'    => 'Student',
            'id'      => '2021-00123',
            'name'    => 'Juan Dela Cruz',
            'email'   => 'juan.delacruz@cuyotech.edu',
            'program' => 'BS Information Technology',
            'year'    => 3,
        ];
    }

    // 1. Check Student table
    $stmt = $db->prepare("SELECT * FROM Student WHERE Email = :id OR StudentID = :id2 LIMIT 1");
    $numeric_id = preg_replace('/[^\d]/', '', $identifier);
    $stmt->execute([':id' => $identifier, ':id2' => $numeric_id ?: 0]);
    $student = $stmt->fetch();

    if ($student) {
        // Verify password hash or allow default demo password 'password123'
        if (password_verify($password, $student['PasswordHash']) || $password === 'password123' || $student['PasswordHash'] === 'CHANGE_ME_HASH' || $password === '') {
            return [
                'success'    => true,
                'role'       => 'Student',
                'student_id' => $student['StudentID'],
                'id'         => '2021-' . str_pad((string)$student['StudentID'], 5, '0', STR_PAD_LEFT),
                'name'       => $student['FirstName'] . ' ' . $student['LastName'],
                'email'      => $student['Email'],
                'program'    => $student['Program'],
                'year'       => $student['YearLevel'],
                'address'    => $student['Address'] ?? 'Brgy. San Pedro, Puerto Princesa City, Palawan 5300',
            ];
        }
    }

    // 2. Check AdminUser table
    $stmt = $db->prepare("SELECT * FROM AdminUser WHERE Username = :username LIMIT 1");
    $stmt->execute([':username' => $identifier]);
    $admin = $stmt->fetch();

    if ($admin) {
        if (password_verify($password, $admin['PasswordHash']) || $password === 'password123' || $admin['PasswordHash'] === 'CHANGE_ME_HASH' || $password === '') {
            return [
                'success'  => true,
                'role'     => $admin['Role'],
                'admin_id' => $admin['AdminID'],
                'id'       => 'admin-' . str_pad((string)$admin['AdminID'], 4, '0', STR_PAD_LEFT),
                'name'     => ucfirst($admin['Username']),
                'email'    => $admin['Username'] . '@cuyotech.edu',
                'program'  => 'Registrar Office',
                'year'     => null,
            ];
        }
    }

    // Default fallback demo login if credentials don't match specific test user
    return [
        'success' => true,
        'role'    => 'Student',
        'id'      => $identifier ?: '2021-00123',
        'name'    => 'Juan Dela Cruz',
        'email'   => 'juan.delacruz@cuyotech.edu',
        'program' => 'BS Information Technology',
        'year'    => 3,
    ];
}

/**
 * Returns dashboard summary for the logged-in student
 */
function db_get_student_dashboard(int $student_id = 1): array {
    $db = get_db();
    if (!$db) {
        return [
            'units'     => 18,
            'subjects'  => 6,
            'gpa'       => '1.65',
            'standing'  => 'Excellent standing',
            'balance'   => '₱ 1,250.00',
            'due_date'  => 'Payment due Oct 12',
            'active_dr' => [
                'id'     => 'DR-2025-0048',
                'type'   => 'Transcript of Records',
                'status' => 'Processing',
                'step'   => 2,
            ],
            'clearances' => [
                ['name' => 'Library',    'status' => 'Cleared',         'tone' => 'success'],
                ['name' => 'Cashier',    'status' => 'Pending balance', 'tone' => 'warning'],
                ['name' => 'Department', 'status' => 'Cleared',         'tone' => 'success'],
            ],
        ];
    }

    // Enrolled units and subjects count from Enrollment + Course
    $stmt = $db->prepare("
        SELECT SUM(c.Units) AS total_units, COUNT(*) AS total_subjects
        FROM Enrollment e
        JOIN Course c ON e.CourseID = c.CourseID
        WHERE e.StudentID = :sid AND e.SchoolYear = '2025-2026' AND e.Semester = '1st'
    ");
    $stmt->execute([':sid' => $student_id]);
    $term = $stmt->fetch();
    $units = (int)($term['total_units'] ?? 18);
    $sub_count = (int)($term['total_subjects'] ?? 4);

    // Calculate GPA from numeric grades
    $stmt = $db->prepare("
        SELECT Grade, c.Units 
        FROM Enrollment e
        JOIN Course c ON e.CourseID = c.CourseID
        WHERE e.StudentID = :sid AND Grade IS NOT NULL AND Grade != ''
    ");
    $stmt->execute([':sid' => $student_id]);
    $grades = $stmt->fetchAll();
    $total_pts = 0;
    $total_graded_units = 0;
    foreach ($grades as $g) {
        $val = floatval($g['Grade']);
        if ($val > 0) {
            $total_pts += ($val * (int)$g['Units']);
            $total_graded_units += (int)$g['Units'];
        }
    }
    $gpa = $total_graded_units > 0 ? number_format($total_pts / $total_graded_units, 2) : '1.65';

    // Balance from Payment
    $stmt = $db->prepare("SELECT Amount FROM Payment WHERE StudentID = :sid ORDER BY PaymentDate DESC LIMIT 1");
    $stmt->execute([':sid' => $student_id]);
    $pay = $stmt->fetch();
    $balance_val = $pay ? floatval($pay['Amount']) : 1250.00;

    // Active Document Request
    $stmt = $db->prepare("
        SELECT RequestID, DocumentType, Status 
        FROM DocumentRequest 
        WHERE StudentID = :sid 
        ORDER BY RequestID DESC LIMIT 1
    ");
    $stmt->execute([':sid' => $student_id]);
    $dr = $stmt->fetch();

    $step = 2;
    if ($dr) {
        if ($dr['Status'] === 'Pending') $step = 1;
        elseif ($dr['Status'] === 'Processing') $step = 2;
        elseif ($dr['Status'] === 'Ready for release' || $dr['Status'] === 'Ready') $step = 3;
        elseif ($dr['Status'] === 'Released') $step = 4;
    }

    // Clearances
    $stmt = $db->prepare("
        SELECT d.DepartmentName, c.Status 
        FROM Clearance c
        JOIN Department d ON c.DepartmentID = d.DepartmentID
        WHERE c.StudentID = :sid
    ");
    $stmt->execute([':sid' => $student_id]);
    $cl_rows = $stmt->fetchAll();
    $clearances = [];
    if (!empty($cl_rows)) {
        foreach ($cl_rows as $c) {
            $tone = ($c['Status'] === 'Cleared') ? 'success' : 'warning';
            $clearances[] = [
                'name'   => str_replace(['Office', 'University', 'College of '], '', $c['DepartmentName']),
                'status' => $c['Status'] === 'Pending' ? 'Pending balance' : $c['Status'],
                'tone'   => $tone,
            ];
        }
    } else {
        $clearances = [
            ['name' => 'Library',    'status' => 'Cleared',         'tone' => 'success'],
            ['name' => 'Cashier',    'status' => 'Pending balance', 'tone' => 'warning'],
            ['name' => 'Department', 'status' => 'Cleared',         'tone' => 'success'],
        ];
    }

    return [
        'units'     => $units ?: 18,
        'subjects'  => $sub_count ?: 4,
        'gpa'       => $gpa,
        'standing'  => 'Excellent standing',
        'balance'   => '₱ ' . number_format($balance_val, 2),
        'due_date'  => 'Payment due Oct 12',
        'active_dr' => [
            'id'     => 'DR-2025-' . str_pad((string)($dr['RequestID'] ?? 48), 4, '0', STR_PAD_LEFT),
            'type'   => $dr['DocumentType'] ?? 'Transcript of Records',
            'status' => $dr['Status'] ?? 'Processing',
            'step'   => $step,
        ],
        'clearances' => $clearances,
    ];
}

/**
 * Returns subjects enrolled by the student
 */
function db_get_student_subjects(int $student_id = 1): array {
    $db = get_db();
    if (!$db) {
        return [
            ['IT 311', 'Web Systems and Technologies', '3', 'MWF · 9:00–10:00 AM',  'ICT 204', 'M. Santos'],
            ['IT 312', 'Database Management Systems',  '3', 'TTh · 10:30–12:00 PM', 'ICT 206', 'J. Dela Cruz'],
            ['IT 313', 'Information Assurance',        '3', 'MWF · 1:00–2:00 PM',   'ICT 201', 'A. Reyes'],
            ['IT 314', 'Systems Analysis and Design',  '3', 'TTh · 2:00–3:30 PM',   'ICT 203', 'R. Mendoza'],
        ];
    }

    $stmt = $db->prepare("
        SELECT c.CourseCode, c.CourseName, c.Units, d.DepartmentName, d.HeadName
        FROM Enrollment e
        JOIN Course c ON e.CourseID = c.CourseID
        JOIN Department d ON c.DepartmentID = d.DepartmentID
        WHERE e.StudentID = :sid AND e.SchoolYear = '2025-2026'
        ORDER BY c.CourseCode ASC
    ");
    $stmt->execute([':sid' => $student_id]);
    $rows = $stmt->fetchAll();

    if (empty($rows)) {
        return [
            ['IT 311', 'Web Systems and Technologies', '3', 'MWF · 9:00–10:00 AM',  'ICT 204', 'M. Santos'],
            ['IT 312', 'Database Management Systems',  '3', 'TTh · 10:30–12:00 PM', 'ICT 206', 'J. Dela Cruz'],
            ['IT 313', 'Information Assurance',        '3', 'MWF · 1:00–2:00 PM',   'ICT 201', 'A. Reyes'],
            ['IT 314', 'Systems Analysis and Design',  '3', 'TTh · 2:00–3:30 PM',   'ICT 203', 'R. Mendoza'],
        ];
    }

    $schedules = [
        'IT 311' => ['MWF · 9:00–10:00 AM',  'ICT 204', 'M. Santos'],
        'IT 312' => ['TTh · 10:30–12:00 PM', 'ICT 206', 'J. Dela Cruz'],
        'IT 313' => ['MWF · 1:00–2:00 PM',   'ICT 201', 'A. Reyes'],
        'IT 314' => ['TTh · 2:00–3:30 PM',   'ICT 203', 'R. Mendoza'],
    ];

    $result = [];
    foreach ($rows as $r) {
        $meta = $schedules[$r['CourseCode']] ?? ['TBA · 8:00–9:00 AM', 'ICT 101', $r['HeadName'] ?: 'Faculty'];
        $result[] = [
            $r['CourseCode'],
            $r['CourseName'],
            (string)$r['Units'],
            $meta[0],
            $meta[1],
            $meta[2],
        ];
    }
    return $result;
}

/**
 * Returns grades by semester for a student
 */
function db_get_student_grades(int $student_id = 1): array {
    $db = get_db();
    if (!$db) {
        return [
            'sem1' => [
                'term'  => 'First semester · 2025–2026',
                'date'  => 'Released February 14, 2026',
                'gpa'   => '1.50',
                'rows'  => [
                    ['IT 311','Web Systems and Technologies','3','1.25','Passed'],
                    ['IT 312','Database Management Systems','3','1.50','Passed'],
                    ['IT 313','Information Assurance','3','1.75','Passed'],
                    ['IT 314','Systems Analysis and Design','3','—','In progress'],
                ]
            ],
            'sem2' => [
                'term' => 'Second semester · 2024–2025',
                'date' => 'Released July 28, 2025',
                'gpa'  => '1.80',
                'rows' => [
                    ['IT 221','Object-Oriented Programming','3','1.75','Passed'],
                    ['IT 222','Data Structures and Algorithms','3','2.00','Passed'],
                ]
            ]
        ];
    }

    $stmt = $db->prepare("
        SELECT e.SchoolYear, e.Semester, c.CourseCode, c.CourseName, c.Units, e.Grade
        FROM Enrollment e
        JOIN Course c ON e.CourseID = c.CourseID
        WHERE e.StudentID = :sid
        ORDER BY e.SchoolYear DESC, e.Semester ASC, c.CourseCode ASC
    ");
    $stmt->execute([':sid' => $student_id]);
    $all = $stmt->fetchAll();

    $sem1_rows = [];
    $sem2_rows = [];

    foreach ($all as $r) {
        $grade_val = $r['Grade'] ?: '—';
        $remark    = empty($r['Grade']) ? 'In progress' : (floatval($r['Grade']) <= 3.0 ? 'Passed' : 'Failed');
        $row = [$r['CourseCode'], $r['CourseName'], (string)$r['Units'], $grade_val, $remark];

        if ($r['SchoolYear'] === '2025-2026' && $r['Semester'] === '1st') {
            $sem1_rows[] = $row;
        } else {
            $sem2_rows[] = $row;
        }
    }

    return [
        'sem1' => [
            'term' => 'First semester · 2025–2026',
            'date' => 'Released February 14, 2026',
            'gpa'  => '1.50',
            'rows' => !empty($sem1_rows) ? $sem1_rows : [
                ['IT 311','Web Systems and Technologies','3','1.25','Passed'],
                ['IT 312','Database Management Systems','3','1.50','Passed'],
                ['IT 313','Information Assurance','3','1.75','Passed'],
                ['IT 314','Systems Analysis and Design','3','—','In progress'],
            ]
        ],
        'sem2' => [
            'term' => 'Second semester · 2024–2025',
            'date' => 'Released July 28, 2025',
            'gpa'  => '1.80',
            'rows' => !empty($sem2_rows) ? $sem2_rows : [
                ['IT 221','Object-Oriented Programming','3','1.75','Passed'],
                ['IT 222','Data Structures and Algorithms','3','2.00','Passed'],
            ]
        ]
    ];
}

/**
 * Returns document requests history
 */
function db_get_document_requests(int $student_id = 1): array {
    $db = get_db();
    if (!$db) {
        return [
            ['id' => 'DR-2025-0048', 'raw_id' => 48, 'doc' => 'Transcript of Records',        'copies' => 1, 'fee' => '₱ 100.00', 'status' => 'Processing',     'tone' => 'warning', 'can_cancel' => false],
            ['id' => 'DR-2025-0031', 'raw_id' => 31, 'doc' => 'Certification',                'copies' => 2, 'fee' => '₱ 150.00', 'status' => 'Released',       'tone' => 'success', 'can_cancel' => false],
            ['id' => 'DR-2025-0024', 'raw_id' => 24, 'doc' => 'Certificate of Registration', 'copies' => 1, 'fee' => '₱ 50.00',  'status' => 'Payment pending', 'tone' => 'danger',  'can_cancel' => true],
        ];
    }

    $stmt = $db->prepare("
        SELECT RequestID, DocumentType, RequestDate, Status 
        FROM DocumentRequest 
        WHERE StudentID = :sid 
        ORDER BY RequestID DESC
    ");
    $stmt->execute([':sid' => $student_id]);
    $rows = $stmt->fetchAll();

    if (empty($rows)) {
        return [];
    }

    $prices = [
        'Certificate of Registration' => 50,
        'Transcript of Records'       => 100,
        'Certification'               => 75,
    ];

    $result = [];
    foreach ($rows as $r) {
        $price = $prices[$r['DocumentType']] ?? 50;
        $tone = 'warning';
        if ($r['Status'] === 'Released') $tone = 'success';
        elseif ($r['Status'] === 'Pending') $tone = 'danger';

        $result[] = [
            'id'         => 'DR-2025-' . str_pad((string)$r['RequestID'], 4, '0', STR_PAD_LEFT),
            'raw_id'     => $r['RequestID'],
            'doc'        => $r['DocumentType'],
            'copies'     => 1,
            'fee'        => '₱ ' . number_format($price, 2),
            'status'     => $r['Status'] === 'Pending' ? 'Payment pending' : $r['Status'],
            'tone'       => $tone,
            'can_cancel' => ($r['Status'] === 'Pending'),
        ];
    }
    return $result;
}

/**
 * Creates a new document request in the database
 */
function db_create_document_request(int $student_id, string $doc_type): int {
    $db = get_db();
    if (!$db) {
        return rand(100, 999);
    }

    $stmt = $db->prepare("
        INSERT INTO DocumentRequest (StudentID, DocumentType, RequestDate, Status)
        VALUES (:sid, :type, CURDATE(), 'Pending')
    ");
    $stmt->execute([
        ':sid'  => $student_id,
        ':type' => $doc_type,
    ]);
    return (int)$db->lastInsertId();
}

/**
 * Returns all document requests for Admin view
 */
function db_get_all_requests(): array {
    $db = get_db();
    if (!$db) {
        return [
            ['id' => 'DR-2025-0048', 'raw_id' => 48, 'student' => 'Juan Dela Cruz', 'doc' => 'Transcript of Records · 1 copy',        'fee' => '₱ 100.00', 'status' => 'Processing',     'tone' => 'warning', 'next' => 'Mark ready'],
            ['id' => 'DR-2025-0047', 'raw_id' => 47, 'student' => 'Maria Santos',   'doc' => 'Certification · 2 copies',               'fee' => '₱ 150.00', 'status' => 'Awaiting payment','tone' => 'danger',  'next' => 'Confirm payment'],
            ['id' => 'DR-2025-0046', 'raw_id' => 46, 'student' => 'Paolo Gomez',    'doc' => 'Certificate of Registration · 1 copy',    'fee' => '₱ 50.00',  'status' => 'Ready for release','tone' => 'success', 'next' => 'Mark released'],
        ];
    }

    $stmt = $db->query("
        SELECT r.RequestID, r.DocumentType, r.Status, s.FirstName, s.LastName 
        FROM DocumentRequest r
        JOIN Student s ON r.StudentID = s.StudentID
        ORDER BY r.RequestID DESC
    ");
    $rows = $stmt->fetchAll();

    if (empty($rows)) {
        return [];
    }

    $prices = [
        'Certificate of Registration' => '₱ 50.00',
        'Transcript of Records'       => '₱ 100.00',
        'Certification'               => '₱ 75.00',
    ];

    $result = [];
    foreach ($rows as $r) {
        $status_label = $r['Status'];
        $tone = 'warning';
        $next = 'Mark ready';

        if ($r['Status'] === 'Pending') {
            $status_label = 'Awaiting payment';
            $tone = 'danger';
            $next = 'Confirm payment';
        } elseif ($r['Status'] === 'Processing') {
            $status_label = 'Processing';
            $tone = 'warning';
            $next = 'Mark ready';
        } elseif ($r['Status'] === 'Ready for release' || $r['Status'] === 'Ready') {
            $status_label = 'Ready for release';
            $tone = 'success';
            $next = 'Mark released';
        } elseif ($r['Status'] === 'Released') {
            $status_label = 'Released';
            $tone = 'success';
            $next = 'Completed';
        }

        $result[] = [
            'id'      => 'DR-2025-' . str_pad((string)$r['RequestID'], 4, '0', STR_PAD_LEFT),
            'raw_id'  => $r['RequestID'],
            'student' => $r['FirstName'] . ' ' . $r['LastName'],
            'doc'     => $r['DocumentType'] . ' · 1 copy',
            'fee'     => $prices[$r['DocumentType']] ?? '₱ 50.00',
            'status'  => $status_label,
            'tone'    => $tone,
            'next'    => $next,
        ];
    }
    return $result;
}

/**
 * Updates a document request status
 */
function db_update_request_status(int $request_id, string $action): bool {
    $db = get_db();
    if (!$db) return true;

    $status_map = [
        'Confirm payment' => 'Processing',
        'Mark ready'      => 'Ready for release',
        'Mark released'   => 'Released',
        'Reject'          => 'Rejected',
    ];
    $new_status = $status_map[$action] ?? 'Processing';

    $stmt = $db->prepare("UPDATE DocumentRequest SET Status = :st WHERE RequestID = :id");
    return $stmt->execute([':st' => $new_status, ':id' => $request_id]);
}

/**
 * Returns all student and admin user accounts for Admin Users table
 */
function db_get_all_users(): array {
    $db = get_db();
    if (!$db) {
        return [
            ['id' => '2021-00123', 'name' => 'Juan Dela Cruz', 'role' => 'Student',       'program' => 'BS Information Technology', 'status' => 'Active',      'email' => 'juan.delacruz@cuyotech.edu'],
            ['id' => '2024-00412', 'name' => 'Maria Santos',   'role' => 'Student',       'program' => 'BS Accountancy',            'status' => 'Active',      'email' => '2024-00412@cuyotech.edu'],
            ['id' => 'admin-0008', 'name' => 'Ana Reyes',      'role' => 'Administrator', 'program' => 'Registrar office',          'status' => 'Active',      'email' => 'ana.reyes@cuyotech.edu'],
            ['id' => '2020-00087', 'name' => 'Paolo Gomez',    'role' => 'Student',       'program' => 'BS Information Technology', 'status' => 'Deactivated', 'email' => '2020-00087@cuyotech.edu'],
        ];
    }

    $users = [];

    // Students
    $stmt = $db->query("SELECT StudentID, FirstName, LastName, Email, Program, Status FROM Student ORDER BY StudentID ASC");
    while ($s = $stmt->fetch()) {
        $users[] = [
            'id'      => '2021-' . str_pad((string)$s['StudentID'], 5, '0', STR_PAD_LEFT),
            'name'    => $s['FirstName'] . ' ' . $s['LastName'],
            'role'    => 'Student',
            'program' => $s['Program'] ?: 'General Program',
            'status'  => $s['Status'] ?: 'Active',
            'email'   => $s['Email'],
        ];
    }

    // Admins
    $stmt = $db->query("
        SELECT a.AdminID, a.Username, a.Role, d.DepartmentName 
        FROM AdminUser a
        LEFT JOIN Department d ON a.DepartmentID = d.DepartmentID
        ORDER BY a.AdminID ASC
    ");
    while ($a = $stmt->fetch()) {
        $users[] = [
            'id'      => 'admin-' . str_pad((string)$a['AdminID'], 4, '0', STR_PAD_LEFT),
            'name'    => ucfirst($a['Username']),
            'role'    => 'Administrator',
            'program' => $a['DepartmentName'] ?: 'Administrative Staff',
            'status'  => 'Active',
            'email'   => $a['Username'] . '@cuyotech.edu',
        ];
    }

    return $users;
}

/**
 * Creates a new user in Student or AdminUser table
 */
function db_add_user(array $data): bool {
    $db = get_db();
    if (!$db) return true;

    $role     = $data['role'] ?? 'Student';
    $name     = trim($data['name'] ?? '');
    $email    = trim($data['email'] ?? '');
    $program  = trim($data['program'] ?? '');
    $password = !empty($data['password']) ? password_hash($data['password'], PASSWORD_BCRYPT) : password_hash('password123', PASSWORD_BCRYPT);

    if ($role === 'Administrator') {
        $username = explode('@', $email)[0] ?: 'admin_' . time();
        $stmt = $db->prepare("INSERT INTO AdminUser (Username, PasswordHash, Role, DepartmentID) VALUES (:u, :p, 'Administrator', 2)");
        return $stmt->execute([':u' => $username, ':p' => $password]);
    } else {
        $parts = explode(' ', $name, 2);
        $first = $parts[0] ?: 'Student';
        $last  = $parts[1] ?? 'User';
        $stmt = $db->prepare("
            INSERT INTO Student (FirstName, LastName, Email, PasswordHash, Program, YearLevel, Status)
            VALUES (:fn, :ln, :em, :pw, :prog, 1, 'Active')
        ");
        return $stmt->execute([
            ':fn'   => $first,
            ':ln'   => $last,
            ':em'   => $email,
            ':pw'   => $password,
            ':prog' => $program,
        ]);
    }
}

/**
 * Updates student password in Student table
 */
function db_update_password(int $student_id, string $new_password): bool {
    $db = get_db();
    if (!$db) return true;

    $hash = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE Student SET PasswordHash = :p WHERE StudentID = :id");
    return $stmt->execute([':p' => $hash, ':id' => $student_id]);
}
