<?php
// FILE: /app/helpers/QuotaHelper.php

class QuotaHelper {

    public static function checkQuota($tenantId, $quotaType) {
        $db = Database::getInstance();

        // Get current subscription and plan
        $subscription = self::getActiveSubscription($tenantId);

        if (!$subscription) {
            return [
                'allowed' => false,
                'message' => 'No active subscription found'
            ];
        }

        // Get current month usage
        $currentMonth = date('Y-m');
        $usage = self::getCurrentUsage($tenantId, $currentMonth);

        // Check quota based on type
        $quotaField = $quotaType . '_count';
        $limitField = 'max_' . $quotaType;

        $currentUsage = $usage[$quotaField] ?? 0;
        $limit = $subscription[$limitField] ?? 0;

        if ($currentUsage >= $limit) {
            return [
                'allowed' => false,
                'message' => 'Monthly quota exceeded',
                'current' => $currentUsage,
                'limit' => $limit
            ];
        }

        return [
            'allowed' => true,
            'current' => $currentUsage,
            'limit' => $limit,
            'remaining' => $limit - $currentUsage
        ];
    }

    public static function incrementUsage($tenantId, $quotaType) {
        $db = Database::getInstance();
        $currentMonth = date('Y-m');

        // Check if usage record exists for current month
        $sql = "SELECT id FROM tenant_usage WHERE tenant_id = :tenant_id AND month = :month";
        $existing = $db->fetch($sql, [
            'tenant_id' => $tenantId,
            'month' => $currentMonth
        ]);

        $quotaField = $quotaType . '_count';

        if ($existing) {
            // Update existing record
            $sql = "UPDATE tenant_usage SET {$quotaField} = {$quotaField} + 1 WHERE id = :id";
            $db->execute($sql, ['id' => $existing['id']]);
        } else {
            // Create new record
            $sql = "INSERT INTO tenant_usage (tenant_id, month, {$quotaField}) VALUES (:tenant_id, :month, 1)";
            $db->execute($sql, [
                'tenant_id' => $tenantId,
                'month' => $currentMonth
            ]);
        }
    }

    private static function getActiveSubscription($tenantId) {
        $db = Database::getInstance();

        $sql = "SELECT ts.*, p.*
                FROM tenant_subscriptions ts
                JOIN plans p ON ts.plan_id = p.id
                WHERE ts.tenant_id = :tenant_id
                AND ts.status = 'active'
                AND (ts.expires_at IS NULL OR ts.expires_at > NOW())
                ORDER BY ts.started_at DESC
                LIMIT 1";

        return $db->fetch($sql, ['tenant_id' => $tenantId]);
    }

    private static function getCurrentUsage($tenantId, $month) {
        $db = Database::getInstance();

        $sql = "SELECT * FROM tenant_usage WHERE tenant_id = :tenant_id AND month = :month";
        $usage = $db->fetch($sql, [
            'tenant_id' => $tenantId,
            'month' => $month
        ]);

        if (!$usage) {
            return [
                'text_generations_count' => 0,
                'image_generations_count' => 0,
                'video_generations_count' => 0,
                'scheduled_posts_count' => 0
            ];
        }

        return $usage;
    }

    public static function getUsageStats($tenantId) {
        $db = Database::getInstance();
        $currentMonth = date('Y-m');

        $subscription = self::getActiveSubscription($tenantId);
        $usage = self::getCurrentUsage($tenantId, $currentMonth);

        if (!$subscription) {
            return null;
        }

        return [
            'plan_name' => $subscription['name'],
            'text_generations' => [
                'used' => $usage['text_generations_count'],
                'limit' => $subscription['max_text_generations'],
                'percentage' => self::calculatePercentage($usage['text_generations_count'], $subscription['max_text_generations'])
            ],
            'image_generations' => [
                'used' => $usage['image_generations_count'],
                'limit' => $subscription['max_image_generations'],
                'percentage' => self::calculatePercentage($usage['image_generations_count'], $subscription['max_image_generations'])
            ],
            'video_generations' => [
                'used' => $usage['video_generations_count'],
                'limit' => $subscription['max_video_generations'],
                'percentage' => self::calculatePercentage($usage['video_generations_count'], $subscription['max_video_generations'])
            ],
            'scheduled_posts' => [
                'used' => $usage['scheduled_posts_count'],
                'limit' => $subscription['max_scheduled_posts'],
                'percentage' => self::calculatePercentage($usage['scheduled_posts_count'], $subscription['max_scheduled_posts'])
            ]
        ];
    }

    private static function calculatePercentage($used, $limit) {
        if ($limit == 0) return 0;
        return round(($used / $limit) * 100, 1);
    }

    public static function hasFeature($tenantId, $feature) {
        $subscription = self::getActiveSubscription($tenantId);

        if (!$subscription || !$subscription['features_json']) {
            return false;
        }

        $features = json_decode($subscription['features_json'], true);
        return in_array($feature, $features);
    }
}
