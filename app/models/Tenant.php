<?php
// FILE: /app/models/Tenant.php

class Tenant extends Model {
    protected $table = 'tenants';
    protected $tenantScope = false; // Disable tenant scoping for tenant model

    public function getActiveSubscription($tenantId) {
        $sql = "SELECT ts.*, p.*
                FROM tenant_subscriptions ts
                JOIN plans p ON ts.plan_id = p.id
                WHERE ts.tenant_id = :tenant_id
                AND ts.status = 'active'
                AND (ts.expires_at IS NULL OR ts.expires_at > NOW())
                ORDER BY ts.started_at DESC
                LIMIT 1";

        return $this->db->fetch($sql, ['tenant_id' => $tenantId]);
    }

    public function createTenant($data) {
        return $this->create($data);
    }

    public function updateSettings($tenantId, $settings) {
        $settingsJson = json_encode($settings);

        $sql = "UPDATE tenants SET settings_json = :settings WHERE id = :id";
        return $this->db->execute($sql, [
            'settings' => $settingsJson,
            'id' => $tenantId
        ]);
    }

    public function getSettings($tenantId) {
        $tenant = $this->findById($tenantId);

        if ($tenant && $tenant['settings_json']) {
            return json_decode($tenant['settings_json'], true);
        }

        return [];
    }
}
