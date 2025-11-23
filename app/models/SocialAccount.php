<?php
// FILE: /app/models/SocialAccount.php

class SocialAccount extends Model {
    protected $table = 'social_accounts';

    public function connectAccount($platform, $username, $accessToken = '') {
        $data = [
            'tenant_id' => $this->getTenantId(),
            'platform' => $platform,
            'username' => $username,
            'access_token' => $accessToken ?: 'simulated_token_' . uniqid(),
            'status' => 'active'
        ];

        return $this->create($data);
    }

    public function getByPlatform($platform) {
        return $this->findAll(['platform' => $platform]);
    }

    public function getAllActive() {
        return $this->findAll(['status' => 'active']);
    }

    public function disconnectAccount($accountId) {
        return $this->update($accountId, ['status' => 'disconnected']);
    }

    public function reconnectAccount($accountId, $newToken = '') {
        $token = $newToken ?: 'simulated_token_' . uniqid();

        return $this->update($accountId, [
            'status' => 'active',
            'access_token' => $token
        ]);
    }

    public function getAccountStats($accountId) {
        $sql = "SELECT
                    COUNT(sp.id) as total_posts,
                    SUM(CASE WHEN sp.status = 'posted' THEN 1 ELSE 0 END) as successful_posts,
                    SUM(CASE WHEN sp.status = 'failed' THEN 1 ELSE 0 END) as failed_posts
                FROM scheduled_posts sp
                WHERE sp.social_account_id = :account_id
                AND sp.tenant_id = :tenant_id";

        return $this->db->fetch($sql, [
            'account_id' => $accountId,
            'tenant_id' => $this->getTenantId()
        ]);
    }
}
