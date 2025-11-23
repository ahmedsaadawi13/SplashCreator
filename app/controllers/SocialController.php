<?php
// FILE: /app/controllers/SocialController.php

class SocialController extends Controller {

    public function index() {
        $this->requireAuth();

        $socialModel = $this->model('SocialAccount');
        $accounts = $socialModel->getAllActive();

        $this->view->render('social/index', [
            'user' => $this->getCurrentUser(),
            'accounts' => $accounts
        ]);
    }

    public function connect() {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'social_manager']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $platform = $_POST['platform'] ?? '';
            $username = $_POST['username'] ?? '';

            $errors = [];

            $allowedPlatforms = ['instagram', 'facebook', 'twitter', 'tiktok', 'linkedin', 'youtube'];
            if (!ValidationHelper::inEnum($platform, $allowedPlatforms)) {
                $errors[] = 'Invalid platform';
            }

            if (!ValidationHelper::required($username)) {
                $errors[] = 'Username is required';
            }

            if (empty($errors)) {
                $socialModel = $this->model('SocialAccount');
                $accountId = $socialModel->connectAccount($platform, $username);

                $this->logActivity('social_account', $accountId, 'connected', "Connected {$platform} account");

                $this->redirect('/social');
                return;
            }

            $this->view->render('social/connect', [
                'user' => $this->getCurrentUser(),
                'errors' => $errors,
                'old' => $_POST,
                'csrf_token' => $this->generateCSRF()
            ]);
        } else {
            $this->view->render('social/connect', [
                'user' => $this->getCurrentUser(),
                'csrf_token' => $this->generateCSRF()
            ]);
        }
    }

    public function disconnect($id) {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'social_manager']);
        $this->validateCSRF();

        $socialModel = $this->model('SocialAccount');
        $socialModel->disconnectAccount($id);

        $this->logActivity('social_account', $id, 'disconnected', 'Disconnected social account');

        $this->json(['status' => 'success']);
    }

    public function stats($id) {
        $this->requireAuth();

        $socialModel = $this->model('SocialAccount');
        $account = $socialModel->findById($id);

        if (!$account) {
            http_response_code(404);
            $this->view->render('errors/404', ['message' => 'Account not found']);
            return;
        }

        $stats = $socialModel->getAccountStats($id);

        $this->view->render('social/stats', [
            'user' => $this->getCurrentUser(),
            'account' => $account,
            'stats' => $stats
        ]);
    }
}
