<?php
namespace App\Models;

class Clearance {
    public ?int $clearanceId;
    public int $studentId;
    public int $departmentId;
    public string $departmentName;
    public string $status;
    public ?string $dateCleared;

    public function __construct(array $data = []) {
        $this->clearanceId    = isset($data['ClearanceID']) ? (int)$data['ClearanceID'] : null;
        $this->studentId      = (int)($data['StudentID'] ?? 0);
        $this->departmentId   = (int)($data['DepartmentID'] ?? 0);
        $this->departmentName = $data['DepartmentName'] ?? 'Office';
        $this->status         = $data['Status'] ?? 'Pending';
        $this->dateCleared    = $data['DateCleared'] ?? null;
    }

    public function getCleanDepartmentName(): string {
        return trim(str_replace(['Office', 'University', 'College of '], '', $this->departmentName));
    }

    public function getStatusLabel(): string {
        return $this->status === 'Pending' ? 'Pending balance' : $this->status;
    }

    public function getStatusTone(): string {
        return $this->status === 'Cleared' ? 'success' : 'warning';
    }
}
