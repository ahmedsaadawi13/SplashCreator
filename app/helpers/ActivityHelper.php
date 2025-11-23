<?php
// FILE: /app/helpers/ActivityHelper.php

class ActivityHelper {

    public static function log($entityType, $entityId, $action, $description = '') {
        $db = Database::getInstance();

        $tenantId = isset($_SESSION['tenant_id']) ? $_SESSION['tenant_id'] : null;
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $data = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'description' => $description,
            'ip_address' => SecurityHelper::getClientIp(),
            'user_agent' => SecurityHelper::getUserAgent()
        ];

        $fields = array_keys($data);
        $sql = "INSERT INTO activity_logs (" . implode(', ', $fields) . ")
                VALUES (:" . implode(', :', $fields) . ")";

        $params = [];
        foreach ($data as $key => $value) {
            $params[":{$key}"] = $value;
        }

        try {
            $db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }

    public static function getRecentActivity($tenantId, $limit = 50) {
        $db = Database::getInstance();

        $sql = "SELECT al.*, u.full_name, u.email
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.tenant_id = :tenant_id
                ORDER BY al.created_at DESC
                LIMIT :limit";

        $stmt = $db->query($sql, ['tenant_id' => $tenantId]);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getUserActivity($userId, $limit = 50) {
        $db = Database::getInstance();

        $sql = "SELECT * FROM activity_logs
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT :limit";

        $stmt = $db->query($sql, ['user_id' => $userId]);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getActivityByEntity($entityType, $entityId, $limit = 50) {
        $db = Database::getInstance();

        $sql = "SELECT al.*, u.full_name, u.email
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.entity_type = :entity_type AND al.entity_id = :entity_id
                ORDER BY al.created_at DESC
                LIMIT :limit";

        $stmt = $db->query($sql, [
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ]);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
