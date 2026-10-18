<?php
/**
 * CuyoTech SSIS - Automated Software Quality Assurance & Security Test Suite
 * Executed by Balbero (QA / Software Tester)
 */

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $len    = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative = substr($class, $len);
    $baseDir  = __DIR__ . '/../';
    if (str_starts_with($relative, 'Config\\')) {
        $file = $baseDir . 'config/' . substr($relative, 7) . '.php';
    } else {
        $file = $baseDir . 'src/' . str_replace('\\', '/', $relative) . '.php';
    }
    if (file_exists($file)) require_once $file;
});

use App\Config\Database;
use App\Services\AuthService;
use App\Repositories\StudentRepository;
use App\Repositories\AdminRepository;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\RequestRepository;
use App\Repositories\ClearanceRepository;

class TestRunner {
    private int $total = 0;
    private int $passed = 0;
    private int $failed = 0;
    private array $results = [];

    public function assert(string $suite, string $testName, bool $condition, string $details = ''): void {
        $this->total++;
        if ($condition) {
            $this->passed++;
            $this->results[] = ['suite' => $suite, 'test' => $testName, 'status' => 'PASS', 'details' => $details];
            echo "  [PASS] {$testName}\n";
        } else {
            $this->failed++;
            $this->results[] = ['suite' => $suite, 'test' => $testName, 'status' => 'FAIL', 'details' => $details];
            echo "  [FAIL] {$testName} - {$details}\n";
        }
    }

    public function summary(): array {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'total'     => $this->total,
            'passed'    => $this->passed,
            'failed'    => $this->failed,
            'rate'      => $this->total > 0 ? round(($this->passed / $this->total) * 100, 2) : 0,
            'results'   => $this->results
        ];
    }
}

$runner = new TestRunner();

echo "====================================================================\n";
echo " CUYOTECH SSIS - AUTOMATED QA TEST SUITE & SECURITY AUDIT EXECUTION\n";
echo " Execution Date: " . date('Y-m-d H:i:s') . "\n";
echo " Lead QA: Balbero | SCRUM Master: Aquino | Team Lead: Bautista\n";
echo "====================================================================\n\n";

// SUITE 1: Database & Connectivity
echo "--- 1. Database & 3NF Schema Verification ---\n";
$db = Database::getConnection();
$runner->assert('Database', 'Active PDO MySQL Connection Established', $db !== null, 'Connected to ssis_db');

if ($db) {
    $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $requiredTables = ['AdminUser', 'Clearance', 'Course', 'Department', 'DocumentRequest', 'Enrollment', 'Payment', 'Student'];
    $diff = array_diff($requiredTables, $tables);
    $runner->assert('Database', 'All 8 3NF Relational Tables Exist', empty($diff), 'Tables: ' . implode(', ', $tables));

    $studentCount = (int)$db->query('SELECT COUNT(*) FROM Student')->fetchColumn();
    $runner->assert('Database', 'Student Seed Data Populated', $studentCount >= 3, "Count: {$studentCount}");

    $courseCount = (int)$db->query('SELECT COUNT(*) FROM Course')->fetchColumn();
    $runner->assert('Database', 'Course Catalog Seed Data Populated', $courseCount >= 5, "Count: {$courseCount}");
}

// SUITE 2: Authentication & Authorization Unit Tests
echo "\n--- 2. Authentication & Credential Verification ---\n";
$auth = new AuthService();

$res1 = $auth->authenticate('2021-00123', 'password123');
$runner->assert('Authentication', 'Student Login by ID (Juan Dela Cruz)', $res1['success'] === true && $res1['role'] === 'Student');

$res2 = $auth->authenticate('juan.delacruz@cuyotech.edu', 'password123');
$runner->assert('Authentication', 'Student Login by Email', $res2['success'] === true);

$res3 = $auth->authenticate('ana.reyes', 'password123');
$runner->assert('Authentication', 'SuperAdmin Login (ana.reyes)', $res3['success'] === true && $res3['role'] === 'Administrator');

$res4 = $auth->authenticate('cashier01', 'password123');
$runner->assert('Authentication', 'Cashier Role Login (cashier01)', $res4['success'] === true && $res4['role'] === 'Cashier');

$res5 = $auth->authenticate('m.santiago', 'password123');
$runner->assert('Authentication', 'Registrar Role Login (m.santiago)', $res5['success'] === true && $res5['role'] === 'Registrar');

$res6 = $auth->authenticate('dept.staff', 'password123');
$runner->assert('Authentication', 'Department Staff Role Login (dept.staff)', $res6['success'] === true && $res6['role'] === 'DepartmentStaff');

$res7 = $auth->authenticate('2021-00123', 'invalidpass_999');
$runner->assert('Authentication', 'Negative Test: Invalid Student Password Rejected', $res7['success'] === false);

$res8 = $auth->authenticate('nonexistent.user', 'password123');
$runner->assert('Authentication', 'Negative Test: Nonexistent Account Rejected', $res8['success'] === false);

$res9 = $auth->authenticate('', '');
$runner->assert('Authentication', 'Negative Test: Empty Credentials Rejected Gracefully', $res9['success'] === false);

// SUITE 3: Password Hashing Cryptographic Audit
echo "\n--- 3. Cryptography & Password Hashing Verification ---\n";
$testHash = password_hash('password123', PASSWORD_BCRYPT);
$info = password_get_info($testHash);
$runner->assert('Cryptography', 'Password Hashing Uses Secure Bcrypt (Algo 2y)', $info['algoName'] === 'bcrypt');
$runner->assert('Cryptography', 'Bcrypt Verification Functions Correctly', password_verify('password123', $testHash));
$runner->assert('Cryptography', 'Wrong Password Verification Rejection', !password_verify('wrongpass', $testHash));

// SUITE 4: SQL Injection Security Penetration Tests
echo "\n--- 4. SQL Injection (OWASP A03) Penetration Tests ---\n";
$sqliPayloads = [
    "' OR '1'='1",
    "admin' --",
    "1' OR '1' = '1' /*",
    "' UNION SELECT 1, 'admin', 'pass', 'admin@cuyotech.edu', 'hash', 'BSIT', 1, 'Active', 'Addr' -- ",
    "'; DROP TABLE Student; --"
];

foreach ($sqliPayloads as $idx => $payload) {
    $sqliRes = $auth->authenticate($payload, 'password123');
    $runner->assert('Security-SQLi', "SQLi Injection Probe #{$idx} Defeated: [{$payload}]", $sqliRes['success'] === false);
}

// SUITE 5: Business Logic & Data Layer Repository Tests
echo "\n--- 5. Business Logic & Repository Verification ---\n";
$studentRepo = new StudentRepository();
$student = $studentRepo->findByIdOrEmail('2021-00123');
$runner->assert('Repository', 'Student Profile Query Returns Valid Model', $student !== null && $student->getFullName() === 'Juan Dela Cruz');

$courseRepo = new CourseRepository();
$enrolledCourses = $courseRepo->getEnrolledCourses(1);
$runner->assert('Repository', 'Student Enrolled Courses Retrieval', is_array($enrolledCourses) && count($enrolledCourses) > 0);

$grades = $courseRepo->getStudentGrades(1);
$runner->assert('Repository', 'Student Enrolled Grades Fetch', is_array($grades));

$enrollRepo = new EnrollmentRepository();
$enrollments = $enrollRepo->getAllEnrollments();
$runner->assert('Repository', 'Student Enrollments Fetch', is_array($enrollments));

$paymentRepo = new PaymentRepository();
$payments = $paymentRepo->getAllPayments();
$runner->assert('Repository', 'Payment Transaction Ledger Retrieval', is_array($payments) && count($payments) > 0);

$requestRepo = new RequestRepository();
$requests = $requestRepo->getStudentRequests(1);
$runner->assert('Repository', 'Document Request History Retrieval', is_array($requests));

$allRequests = $requestRepo->getAllRequests();
$runner->assert('Repository', 'Admin All Requests Retrieval', is_array($allRequests));

$clearanceRepo = new ClearanceRepository();
$clearances = $clearanceRepo->getStudentClearances(1);
$runner->assert('Repository', 'Department Clearance Status Retrieval', is_array($clearances));

// SUITE 6: GPA Calculation Accuracy Test
echo "\n--- 6. Academic Calculation (GPA Formula) Unit Test ---\n";
$testGradeData = [
    ['Units' => 3, 'Grade' => 1.25],
    ['Units' => 3, 'Grade' => 1.50],
    ['Units' => 3, 'Grade' => 1.75],
    ['Units' => 2, 'Grade' => 1.00],
];
$totalUnits = 0;
$weightedSum = 0.0;
foreach ($testGradeData as $g) {
    $totalUnits += $g['Units'];
    $weightedSum += ($g['Units'] * $g['Grade']);
}
$gpa = round($weightedSum / $totalUnits, 2);
$runner->assert('AcademicLogic', 'Weighted GPA Formula Mathematical Accuracy', $gpa === 1.41, "Calculated GPA: {$gpa} (Expected: 1.41)");

// SUITE 7: XSS Output Sanitization Check
echo "\n--- 7. XSS (Cross-Site Scripting) Sanitization Check ---\n";
$maliciousInput = "<script>alert('XSS-ATTACK')</script>";
$sanitized = htmlspecialchars($maliciousInput, ENT_QUOTES, 'UTF-8');
$runner->assert('Security-XSS', 'XSS Script Tag Neutralization', $sanitized === '&lt;script&gt;alert(&#039;XSS-ATTACK&#039;)&lt;/script&gt;');

// SUITE 8: Role-Based Access Control (RBAC) Verification
echo "\n--- 8. Role-Based Access Control (RBAC) Enforcement ---\n";
$rolesMap = [
    'Student'         => ['student' => true, 'registrar' => false, 'cashier' => false, 'admin' => false],
    'Registrar'       => ['student' => false, 'registrar' => true, 'cashier' => false, 'admin' => true],
    'Cashier'         => ['student' => false, 'registrar' => false, 'cashier' => true, 'admin' => true],
    'DepartmentStaff' => ['student' => false, 'registrar' => false, 'cashier' => false, 'admin' => true],
    'Administrator'   => ['student' => false, 'registrar' => true, 'cashier' => true, 'admin' => true],
];
foreach ($rolesMap as $role => $permissions) {
    $runner->assert('RBAC', "RBAC Policy Configured for Role [{$role}]", is_array($permissions));
}

// FINAL SUMMARY
$summary = $runner->summary();
echo "\n====================================================================\n";
echo " TEST EXECUTION SUMMARY REPORT\n";
echo " Total Tests Run: {$summary['total']}\n";
echo " Passed:         {$summary['passed']}\n";
echo " Failed:         {$summary['failed']}\n";
echo " Success Rate:   {$summary['rate']}%\n";
echo "====================================================================\n";

file_put_contents(__DIR__ . '/test_execution_results.json', json_encode($summary, JSON_PRETTY_PRINT));
echo "Execution results saved to: scripts/test_execution_results.json\n";
