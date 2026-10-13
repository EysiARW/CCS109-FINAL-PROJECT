<?php
namespace App\Controllers;

use App\Repositories\AdminRepository;
use App\Services\RequestService;
use App\Utils\View;

class AdminController {
    private AdminRepository $adminRepo;
    private RequestService $requestService;

    public function __construct() {
        $this->adminRepo      = new AdminRepository();
        $this->requestService = new RequestService();
    }

    public function users(): void {
        $users     = $this->adminRepo->getAllUsers();
        $userAdded = !empty($_GET['user_added']);
        $userError = $_GET['user_error'] ?? null;

        $errorMessage = match ($userError) {
            'empty_fields'    => 'Full name and email address are required.',
            'creation_failed' => 'Could not create account. Email or username may already be in use.',
            default           => null,
        };

        View::render('admin/users', [
            'page'         => 'admin-users',
            'users'        => $users,
            'userAdded'    => $userAdded,
            'errorMessage' => $errorMessage,
        ]);
    }

    public function addUser(): void {
        $email = trim($_POST['email'] ?? '');
        $name  = trim($_POST['name'] ?? '');

        if ($email === '' || $name === '') {
            header('Location: ?page=admin-users&user_error=empty_fields');
            exit;
        }

        $ok = $this->adminRepo->createUser($_POST);
        if ($ok) {
            header('Location: ?page=admin-users&user_added=1');
        } else {
            header('Location: ?page=admin-users&user_error=creation_failed');
        }
        exit;
    }

    public function requests(): void {
        $requests = $this->requestService->getAllRequests();
        $statusUpdated = !empty($_GET['status_updated']);

        View::render('admin/requests', [
            'page'          => 'admin-requests',
            'requests'      => $requests,
            'statusUpdated' => $statusUpdated,
        ]);
    }

    public function updateRequestStatus(): void {
        $requestId = (int)($_POST['request_id'] ?? 0);
        $action    = $_POST['status_action'] ?? '';

        if ($requestId > 0 && !empty($action)) {
            $this->requestService->updateRequestStatus($requestId, $action);
        }

        header('Location: ?page=admin-requests&status_updated=1');
        exit;
    }
}
