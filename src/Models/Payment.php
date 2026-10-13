<?php
namespace App\Models;

class Payment {
    public ?int $paymentId;
    public int $studentId;
    public float $amount;
    public string $paymentDate;
    public string $orNumber;
    public string $paymentType;

    public function __construct(array $data = []) {
        $this->paymentId   = isset($data['PaymentID']) ? (int)$data['PaymentID'] : null;
        $this->studentId   = (int)($data['StudentID'] ?? 0);
        $this->amount      = isset($data['Amount']) ? (float)$data['Amount'] : 1250.00;
        $this->paymentDate = $data['PaymentDate'] ?? date('Y-m-d');
        $this->orNumber    = $data['ORNumber'] ?? 'OR-2025-0182';
        $this->paymentType = $data['PaymentType'] ?? 'Tuition';
    }

    public function getFormattedAmount(): string {
        return '₱ ' . number_format($this->amount, 2);
    }
}
