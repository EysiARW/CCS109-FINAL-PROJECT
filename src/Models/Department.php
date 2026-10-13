<?php
namespace App\Models;

class Department {
    public ?int $departmentId;
    public string $departmentName;
    public ?string $headName;

    public function __construct(array $data = []) {
        $this->departmentId   = isset($data['DepartmentID']) ? (int)$data['DepartmentID'] : null;
        $this->departmentName = $data['DepartmentName'] ?? '';
        $this->headName       = $data['HeadName'] ?? null;
    }
}
