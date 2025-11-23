<?php
// FILE: /app/core/Model.php

class Model {
    protected $db;
    protected $table;
    protected $tenantScope = true; // Enable tenant isolation by default

    public function __construct() {
        $this->db = Database::getInstance();
    }

    protected function getTenantId() {
        return isset($_SESSION['tenant_id']) ? $_SESSION['tenant_id'] : null;
    }

    protected function applyTenantScope(&$sql, &$params) {
        if ($this->tenantScope && $this->getTenantId()) {
            if (stripos($sql, 'WHERE') !== false) {
                $sql .= " AND tenant_id = :tenant_id_scope";
            } else {
                $sql .= " WHERE tenant_id = :tenant_id_scope";
            }
            $params['tenant_id_scope'] = $this->getTenantId();
        }
    }

    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $params = ['id' => $id];
        $this->applyTenantScope($sql, $params);
        $sql .= " LIMIT 1";
        return $this->db->fetch($sql, $params);
    }

    public function findAll($conditions = [], $orderBy = 'created_at DESC', $limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $key => $value) {
                $whereClause[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }

        $this->applyTenantScope($sql, $params);

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function create($data) {
        if ($this->tenantScope && $this->getTenantId() && !isset($data['tenant_id'])) {
            $data['tenant_id'] = $this->getTenantId();
        }

        $fields = array_keys($data);
        $values = array_values($data);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ")
                VALUES (:" . implode(', :', $fields) . ")";

        $params = [];
        foreach ($data as $key => $value) {
            $params[":{$key}"] = $value;
        }

        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $setParts = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $this->applyTenantScope($sql, $params);

        return $this->db->execute($sql, $params);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $params = ['id' => $id];
        $this->applyTenantScope($sql, $params);

        return $this->db->execute($sql, $params);
    }

    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $key => $value) {
                $whereClause[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }

        $this->applyTenantScope($sql, $params);

        $result = $this->db->fetch($sql, $params);
        return $result['count'];
    }

    protected function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }

    protected function fetch($sql, $params = []) {
        return $this->db->fetch($sql, $params);
    }

    protected function fetchAll($sql, $params = []) {
        return $this->db->fetchAll($sql, $params);
    }

    protected function execute($sql, $params = []) {
        return $this->db->execute($sql, $params);
    }
}
