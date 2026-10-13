<?php
namespace App\Controllers;

use App\Repositories\PaymentRepository;
use App\Utils\View;

class CashierController {
    private PaymentRepository $paymentRepo;

    public function __construct() {
        $this->paymentRepo = new PaymentRepository();
    }

    public function payments(): void {
        $payments = $this->paymentRepo->getAllPayments();
        $students = $this->paymentRepo->getAllStudents();

        $paymentRecorded = !empty($_GET['payment_recorded']);
        $newOr           = $_GET['new_or'] ?? null;
        $error           = $_GET['error'] ?? null;

        $errorMessage = match ($error) {
            'invalid_amount' => 'Please enter a valid payment amount greater than zero.',
            'missing_fields' => 'Please select a student and payment type.',
            'failed'         => 'Could not record transaction. Please try again.',
            default          => null,
        };

        View::render('cashier/payments', [
            'page'            => 'payments',
            'payments'        => $payments,
            'students'        => $students,
            'paymentRecorded' => $paymentRecorded,
            'newOr'           => $newOr,
            'errorMessage'    => $errorMessage,
        ]);
    }

    public function recordPayment(): void {
        $studentId    = (int)($_POST['student_id'] ?? 0);
        $studentInput = trim((string)($_POST['student_search'] ?? $_POST['student_input'] ?? ''));

        if ($studentId <= 0 && $studentInput !== '') {
            $found = $this->paymentRepo->findStudentByNameOrCode($studentInput);
            if ($found) {
                $studentId = $found['id'];
            }
        }

        $amount      = (float)($_POST['amount'] ?? 0);
        $paymentType = trim($_POST['payment_type'] ?? 'Tuition');
        $orNumber    = trim($_POST['or_number'] ?? '');

        if ($studentId <= 0 || empty($paymentType)) {
            header('Location: ?page=payments&error=missing_fields');
            exit;
        }

        if ($amount <= 0) {
            header('Location: ?page=payments&error=invalid_amount');
            exit;
        }

        if (empty($orNumber)) {
            $orNumber = 'OR-' . date('Y') . '-' . str_pad((string)rand(100, 9999), 4, '0', STR_PAD_LEFT);
        }

        $ok = $this->paymentRepo->recordPayment($studentId, $amount, $paymentType, $orNumber);
        if ($ok) {
            header('Location: ?page=payments&payment_recorded=1&new_or=' . urlencode($orNumber));
        } else {
            header('Location: ?page=payments&error=failed');
        }
        exit;
    }

    public function receipts(): void {
        $payments = $this->paymentRepo->getAllPayments();

        View::render('cashier/receipts', [
            'page'     => 'receipts',
            'payments' => $payments,
        ]);
    }
}
