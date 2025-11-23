<?php
// FILE: /app/core/Controller.php

class Controller {
    protected $view;
    protected $db;

    public function __construct() {
        $this->view = new View();
        $this->db = Database::getInstance();

        // Session management
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function model($model) {
        $modelPath = __DIR__ . '/../models/' . $model . '.php';
        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        }
        throw new Exception("Model {$model} not found");
    }

    protected function helper($helper) {
        $helperPath = __DIR__ . '/../helpers/' . $helper . '.php';
        if (file_exists($helperPath)) {
            require_once $helperPath;
            return true;
        }
        throw new Exception("Helper {$helper} not found");
    }

    protected function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit;
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function getCurrentUser() {
        return isset($_SESSION['user_id']) ? $_SESSION : null;
    }

    protected function getCurrentTenantId() {
        return isset($_SESSION['tenant_id']) ? $_SESSION['tenant_id'] : null;
    }

    protected function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/auth/login');
        }
    }

    protected function requireRole($roles) {
        $this->requireAuth();

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!in_array($_SESSION['role'], $roles)) {
            $this->view->render('errors/403', ['message' => 'Access denied']);
            exit;
        }
    }

    protected function validateCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
                http_response_code(403);
                die('CSRF token validation failed');
            }
        }
    }

    protected function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function logActivity($entityType, $entityId, $action, $description = '') {
        $this->helper('ActivityHelper');
        ActivityHelper::log($entityType, $entityId, $action, $description);
    }
}
