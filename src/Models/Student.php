<?php
namespace App\Models;

class Student {
    public ?int $studentId;
    public string $firstName;
    public string $lastName;
    public string $email;
    public string $passwordHash;
    public ?string $program;
    public ?int $yearLevel;
    public string $status;
    public ?string $address;

    public function __construct(array $data = []) {
        $this->studentId    = isset($data['StudentID']) ? (int)$data['StudentID'] : null;
        $this->firstName    = $data['FirstName'] ?? '';
        $this->lastName     = $data['LastName'] ?? '';
        $this->email        = $data['Email'] ?? '';
        $this->passwordHash = $data['PasswordHash'] ?? '';
        $this->program      = $data['Program'] ?? 'BS Information Technology';
        $this->yearLevel    = isset($data['YearLevel']) ? (int)$data['YearLevel'] : 3;
        $this->status       = $data['Status'] ?? 'Active';
        $this->address      = $data['Address'] ?? 'Brgy. San Pedro, Puerto Princesa City, Palawan 5300';
    }

    public function getFullName(): string {
        return trim("{$this->firstName} {$this->lastName}");
    }

    public function getFormattedId(): string {
        if (!empty($this->email) && preg_match('/^(\d{4}-\d{5})@/', $this->email, $m)) {
            return $m[1];
        }
        if ($this->studentId === 1) {
            return '2021-00123';
        }
        return '2021-' . str_pad((string)($this->studentId ?? 123), 5, '0', STR_PAD_LEFT);
    }

    public function getYearLevelFormatted(): string {
        return match ($this->yearLevel) {
            1       => '1st year',
            2       => '2nd year',
            3       => '3rd year',
            4       => '4th year',
            default => ($this->yearLevel ? "{$this->yearLevel}th year" : 'N/A'),
        };
    }
}
