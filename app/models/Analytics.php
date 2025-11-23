<?php
// FILE: /app/models/Analytics.php

class Analytics extends Model {
    protected $table = 'social_metrics';

    public function saveMetrics($data) {
        return $this->create($data);
    }

    public function getMetricsByPlatform($platform, $limit = 30) {
        $sql = "SELECT sm.*, ci.title, ci.type
                FROM social_metrics sm
                LEFT JOIN content_items ci ON sm.content_item_id = ci.id
                WHERE sm.tenant_id = :tenant_id AND sm.platform = :platform
                ORDER BY sm.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->query($sql, [
            'tenant_id' => $this->getTenantId(),
            'platform' => $platform
        ]);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getOverallMetrics() {
        $sql = "SELECT
                    platform,
                    COUNT(*) as post_count,
                    SUM(impressions) as total_impressions,
                    SUM(likes) as total_likes,
                    SUM(comments) as total_comments,
                    SUM(shares) as total_shares,
                    SUM(clicks) as total_clicks
                FROM social_metrics
                WHERE tenant_id = :tenant_id
                GROUP BY platform";

        return $this->db->fetchAll($sql, ['tenant_id' => $this->getTenantId()]);
    }

    public function getContentPerformance($contentItemId) {
        $sql = "SELECT * FROM social_metrics
                WHERE tenant_id = :tenant_id
                AND content_item_id = :content_item_id
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, [
            'tenant_id' => $this->getTenantId(),
            'content_item_id' => $contentItemId
        ]);
    }

    public function getTopPerformingContent($metric = 'impressions', $limit = 10) {
        $allowedMetrics = ['impressions', 'likes', 'comments', 'shares', 'clicks'];

        if (!in_array($metric, $allowedMetrics)) {
            $metric = 'impressions';
        }

        $sql = "SELECT
                    sm.content_item_id,
                    ci.title,
                    ci.type,
                    SUM(sm.{$metric}) as total_metric,
                    SUM(sm.impressions) as total_impressions,
                    SUM(sm.likes) as total_likes,
                    SUM(sm.comments) as total_comments,
                    SUM(sm.shares) as total_shares
                FROM social_metrics sm
                JOIN content_items ci ON sm.content_item_id = ci.id
                WHERE sm.tenant_id = :tenant_id
                GROUP BY sm.content_item_id, ci.title, ci.type
                ORDER BY total_metric DESC
                LIMIT :limit";

        $stmt = $this->db->query($sql, ['tenant_id' => $this->getTenantId()]);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getDashboardStats() {
        $tenantId = $this->getTenantId();

        $sql = "SELECT
                    COUNT(DISTINCT sm.content_item_id) as unique_posts,
                    SUM(sm.impressions) as total_impressions,
                    SUM(sm.likes) as total_likes,
                    SUM(sm.comments) as total_comments,
                    SUM(sm.shares) as total_shares,
                    SUM(sm.clicks) as total_clicks,
                    AVG(sm.impressions) as avg_impressions,
                    AVG(sm.likes) as avg_likes
                FROM social_metrics sm
                WHERE sm.tenant_id = :tenant_id";

        return $this->db->fetch($sql, ['tenant_id' => $tenantId]);
    }
}
