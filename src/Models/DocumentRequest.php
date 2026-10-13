<?php
namespace App\Models;

class DocumentRequest {
    public ?int $requestId;
    public int $studentId;
    public string $documentType;
    public int $copies;
    public string $requestDate;
    public string $status;
    public ?int $processedByAdminId;
    public ?string $studentName;

    public function __construct(array $data = []) {
        $this->requestId          = isset($data['RequestID']) ? (int)$data['RequestID'] : null;
        $this->studentId          = (int)($data['StudentID'] ?? 0);
        $this->documentType       = $data['DocumentType'] ?? 'Certificate of Registration';
        $this->copies             = max(1, (int)($data['Copies'] ?? 1));
        $this->requestDate        = $data['RequestDate'] ?? date('Y-m-d');
        $this->status             = $data['Status'] ?? 'Pending';
        $this->processedByAdminId = isset($data['ProcessedByAdminID']) ? (int)$data['ProcessedByAdminID'] : null;
        $this->studentName        = $data['StudentName'] ?? '';
    }

    public function getFormattedId(): string {
        return 'DR-2025-' . str_pad((string)($this->requestId ?? 48), 4, '0', STR_PAD_LEFT);
    }

    public function getFee(): string {
        $pricePerCopy = match ($this->documentType) {
            'Transcript of Records' => 100,
            'Certification'         => 75,
            default                 => 50,
        };

        return '₱ ' . number_format($pricePerCopy * $this->copies, 2);
    }

    public function getStatusTone(): string {
        return match ($this->status) {
            'Released', 'Ready for release', 'Ready' => 'success',
            'Processing'                             => 'warning',
            'Pending'                                => 'pending',
            'Cancelled', 'Rejected'                  => 'danger',
            default                                  => 'danger',
        };
    }

    public function getStatusLabel(): string {
        return $this->status === 'Pending' ? 'Payment pending' : $this->status;
    }

    public function getNextAction(): string {
        return match ($this->status) {
            'Pending'           => 'Confirm payment',
            'Processing'        => 'Mark ready',
            'Ready for release' => 'Mark released',
            default             => 'Completed',
        };
    }
}
