<?php
// FILE: /app/controllers/AuthController.php

class AuthController extends Controller {

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (!ValidationHelper::email($email)) {
                $this->view->render('auth/login', ['error' => 'Invalid email address']);
                return;
            }

            $userModel = $this->model('User');
            $user = $userModel->authenticate($email, $password);

            if ($user) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['tenant_id'] = $user['tenant_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                $userModel->updateLastLogin($user['id']);

                $this->logActivity('user', $user['id'], 'login', 'User logged in');

                $this->redirect('/dashboard');
            } else {
                $this->view->render('auth/login', ['error' => 'Invalid credentials']);
            }
        } else {
            $this->view->render('auth/login', ['csrf_token' => $this->generateCSRF()]);
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $errors = [];

            $companyName = $_POST['company_name'] ?? '';
            $subdomain = $_POST['subdomain'] ?? '';
            $email = $_POST['email'] ?? '';
            $fullName = $_POST['full_name'] ?? '';
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            // Validation
            if (!ValidationHelper::required($companyName)) {
                $errors[] = 'Company name is required';
            }

            if (!ValidationHelper::required($subdomain) || !preg_match('/^[a-z0-9-]+$/', $subdomain)) {
                $errors[] = 'Subdomain is invalid';
            }

            if (!ValidationHelper::email($email)) {
                $errors[] = 'Invalid email address';
            }

            if (!ValidationHelper::minLength($password, 8)) {
                $errors[] = 'Password must be at least 8 characters';
            }

            if ($password !== $passwordConfirm) {
                $errors[] = 'Passwords do not match';
            }

            if (empty($errors)) {
                $tenantModel = $this->model('Tenant');
                $userModel = $this->model('User');

                // Check if subdomain exists
                $existing = $tenantModel->findAll(['subdomain' => $subdomain]);
                if (!empty($existing)) {
                    $errors[] = 'Subdomain already taken';
                }

                // Check if email exists
                $existingUser = $userModel->findByEmail($email);
                if ($existingUser) {
                    $errors[] = 'Email already registered';
                }

                if (empty($errors)) {
                    // Create tenant
                    $tenantId = $tenantModel->create([
                        'company_name' => $companyName,
                        'subdomain' => $subdomain,
                        'status' => 'active',
                        'owner_email' => $email
                    ]);

                    // Create subscription (default to Starter plan)
                    $sql = "INSERT INTO tenant_subscriptions (tenant_id, plan_id, status, started_at)
                            SELECT :tenant_id, id, 'active', NOW() FROM plans WHERE name = 'Starter' LIMIT 1";
                    $this->db->execute($sql, ['tenant_id' => $tenantId]);

                    // Create user
                    $userId = $userModel->createUser([
                        'tenant_id' => $tenantId,
                        'email' => $email,
                        'password' => $password,
                        'full_name' => $fullName,
                        'role' => 'tenant_admin',
                        'is_active' => 1
                    ]);

                    // Log in the user
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['tenant_id'] = $tenantId;
                    $_SESSION['email'] = $email;
                    $_SESSION['full_name'] = $fullName;
                    $_SESSION['role'] = 'tenant_admin';

                    $this->redirect('/dashboard');
                    return;
                }
            }

            $this->view->render('auth/register', [
                'errors' => $errors,
                'csrf_token' => $this->generateCSRF(),
                'old' => $_POST
            ]);
        } else {
            $this->view->render('auth/register', ['csrf_token' => $this->generateCSRF()]);
        }
    }

    public function logout() {
        $this->logActivity('user', $_SESSION['user_id'] ?? null, 'logout', 'User logged out');

        session_destroy();
        $this->redirect('/auth/login');
    }
}
