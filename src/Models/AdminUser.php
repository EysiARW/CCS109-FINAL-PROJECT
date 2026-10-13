<?php
namespace App\Models;

class AdminUser {
    public ?int $adminId;
    public string $username;
    public string $passwordHash;
    public string $role;
    public ?int $departmentId;
    public ?string $departmentName;

    public function __construct(array $data = []) {
        $this->adminId        = isset($data['AdminID']) ? (int)$data['AdminID'] : null;
        $this->username       = $data['Username'] ?? '';
        $this->passwordHash   = $data['PasswordHash'] ?? '';
        $this->role           = $data['Role'] ?? 'Administrator';
        $this->departmentId   = isset($data['DepartmentID']) ? (int)$data['DepartmentID'] : null;
        $this->departmentName = $data['DepartmentName'] ?? 'Registrar Office';
    }

    public function getFormattedId(): string {
        return 'admin-' . str_pad((string)($this->adminId ?? 8), 4, '0', STR_PAD_LEFT);
    }
}
