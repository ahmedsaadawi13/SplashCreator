<?php
// FILE: /app/models/User.php

class User extends Model {
    protected $table = 'users';

    public function authenticate($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email AND is_active = 1 LIMIT 1";
        $user = $this->db->fetch($sql, ['email' => $email]);

        if ($user && SecurityHelper::verifyPassword($password, $user['password_hash'])) {
            return $user;
        }

        return false;
    }

    public function createUser($data) {
        // Hash password before storing
        if (isset($data['password'])) {
            $data['password_hash'] = SecurityHelper::hashPassword($data['password']);
            unset($data['password']);
        }

        return $this->create($data);
    }

    public function updatePassword($userId, $newPassword) {
        $passwordHash = SecurityHelper::hashPassword($newPassword);

        $sql = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
        return $this->db->execute($sql, [
            'password_hash' => $passwordHash,
            'id' => $userId
        ]);
    }

    public function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login_at = NOW() WHERE id = :id";
        return $this->db->execute($sql, ['id' => $userId]);
    }

    public function getUsersByTenant($tenantId) {
        $sql = "SELECT id, email, full_name, role, is_active, last_login_at, created_at
                FROM users
                WHERE tenant_id = :tenant_id
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, ['tenant_id' => $tenantId]);
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        return $this->db->fetch($sql, ['email' => $email]);
    }

    public function hasPermission($userId, $permission) {
        $user = $this->findById($userId);

        if (!$user) return false;

        $rolePermissions = [
            'platform_admin' => ['all'],
            'tenant_admin' => ['manage_users', 'manage_content', 'manage_schedule', 'manage_social', 'view_analytics', 'manage_brand'],
            'content_creator' => ['create_content', 'manage_content', 'view_analytics'],
            'social_manager' => ['manage_schedule', 'manage_social', 'view_analytics'],
            'viewer' => ['view_content', 'view_analytics']
        ];

        $permissions = $rolePermissions[$user['role']] ?? [];

        return in_array('all', $permissions) || in_array($permission, $permissions);
    }
}
