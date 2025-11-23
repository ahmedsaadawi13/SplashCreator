<?php
// FILE: /app/models/Template.php

class Template extends Model {
    protected $table = 'templates';
    protected $tenantScope = false; // Templates can be global or tenant-specific

    public function getTemplatesByType($type) {
        $sql = "SELECT * FROM templates
                WHERE type = :type
                AND (tenant_id IS NULL OR tenant_id = :tenant_id)
                ORDER BY tenant_id DESC, created_at DESC";

        return $this->db->fetchAll($sql, [
            'type' => $type,
            'tenant_id' => $this->getTenantId()
        ]);
    }

    public function getGlobalTemplates($type = null) {
        $sql = "SELECT * FROM templates WHERE tenant_id IS NULL";
        $params = [];

        if ($type) {
            $sql .= " AND type = :type";
            $params['type'] = $type;
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function getTenantTemplates($type = null) {
        $sql = "SELECT * FROM templates WHERE tenant_id = :tenant_id";
        $params = ['tenant_id' => $this->getTenantId()];

        if ($type) {
            $sql .= " AND type = :type";
            $params['type'] = $type;
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function createTemplate($data) {
        if (!isset($data['tenant_id'])) {
            $data['tenant_id'] = $this->getTenantId();
        }

        return $this->create($data);
    }
}
