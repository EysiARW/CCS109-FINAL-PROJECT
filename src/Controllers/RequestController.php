<?php
namespace App\Controllers;

use App\Services\RequestService;
use App\Utils\View;

class RequestController {
    private RequestService $requestService;

    public function __construct() {
        $this->requestService = new RequestService();
    }

    public function index(): void {
        $studentId = (int)($_SESSION['student_id'] ?? 1);
        $requests  = $this->requestService->getStudentRequests($studentId);

        $selectedDoc = $_POST['doc_type'] ?? 'Certificate of Registration';
        $submitted   = !empty($_GET['submitted']);
        $cancelled   = !empty($_GET['cancelled']);
        $requestError = !empty($_GET['error']) ? (string)$_GET['error'] : null;
        $newId       = $_GET['new_id'] ?? null;

        $docs = [
            ['Certificate of Registration', 'Proof of current enrollment',           '₱ 50.00'],
            ['Transcript of Records',        'Official record of academic performance','₱ 100.00'],
            ['Certification',                'General student certification',          '₱ 75.00'],
        ];

        View::render('student/requests', [
            'page'        => 'requests',
            'requests'    => $requests,
            'selectedDoc' => $selectedDoc,
            'submitted'   => $submitted,
            'cancelled'   => $cancelled,
            'requestError' => $requestError,
            'newId'       => $newId,
            'docs'        => $docs,
        ]);
    }

    public function submit(): void {
        $studentId = (int)($_SESSION['student_id'] ?? 1);
        $docType   = $_POST['doc_type'] ?? 'Certificate of Registration';
        $copies    = filter_var($_POST['copies'] ?? 1, FILTER_VALIDATE_INT);
        if ($copies === false || $copies < 1 || $copies > 5) {
            header('Location: ?page=requests&error=invalid_copies');
            exit;
        }

        $newId = $this->requestService->submitRequest($studentId, $docType, $copies);
        header('Location: ?page=requests&submitted=1&new_id=' . $newId);
        exit;
    }

    public function cancel(): void {
        $studentId = (int)($_SESSION['student_id'] ?? 1);
        $requestId = (int)($_POST['request_id'] ?? 0);

        if ($requestId > 0) {
            $this->requestService->cancelRequest($requestId, $studentId);
        }

        header('Location: ?page=requests&cancelled=1');
        exit;
    }
}
