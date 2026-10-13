<?php
namespace App\Services;

use App\Repositories\RequestRepository;

class RequestService {
    private RequestRepository $requestRepo;

    public function __construct(?RequestRepository $requestRepo = null) {
        $this->requestRepo = $requestRepo ?? new RequestRepository();
    }

    public function submitRequest(int $studentId, string $docType, int $copies): int {
        return $this->requestRepo->create($studentId, $docType, $copies);
    }

    public function getStudentRequests(int $studentId): array {
        return $this->requestRepo->getStudentRequests($studentId);
    }

    public function getAllRequests(): array {
        return $this->requestRepo->getAllRequests();
    }

    public function updateRequestStatus(int $requestId, string $action): bool {
        return $this->requestRepo->updateStatus($requestId, $action);
    }

    public function cancelRequest(int $requestId, int $studentId): bool {
        return $this->requestRepo->cancelRequest($requestId, $studentId);
    }
}
