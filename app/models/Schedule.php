<?php
// FILE: /app/models/Schedule.php

class Schedule extends Model {
    protected $table = 'scheduled_posts';

    public function schedulePost($data) {
        // Increment quota
        QuotaHelper::incrementUsage($this->getTenantId(), 'scheduled_posts');

        return $this->create($data);
    }

    public function getPendingPosts() {
        $sql = "SELECT sp.*, ci.title, ci.content_text, ci.media_path, ci.type,
                       sa.platform as account_platform, sa.username
                FROM scheduled_posts sp
                JOIN content_items ci ON sp.content_item_id = ci.id
                JOIN social_accounts sa ON sp.social_account_id = sa.id
                WHERE sp.status = 'scheduled'
                AND sp.scheduled_time_utc <= NOW()
                ORDER BY sp.scheduled_time_utc ASC";

        return $this->db->fetchAll($sql);
    }

    public function getUpcomingPosts($limit = 50) {
        $sql = "SELECT sp.*, ci.title, ci.content_text, ci.type,
                       sa.platform, sa.username
                FROM scheduled_posts sp
                JOIN content_items ci ON sp.content_item_id = ci.id
                JOIN social_accounts sa ON sp.social_account_id = sa.id
                WHERE sp.tenant_id = :tenant_id
                AND sp.status = 'scheduled'
                AND sp.scheduled_time_utc > NOW()
                ORDER BY sp.scheduled_time_utc ASC
                LIMIT :limit";

        $stmt = $this->db->query($sql, ['tenant_id' => $this->getTenantId()]);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getPostedHistory($limit = 50) {
        $sql = "SELECT sp.*, ci.title, ci.content_text, ci.type,
                       sa.platform, sa.username
                FROM scheduled_posts sp
                JOIN content_items ci ON sp.content_item_id = ci.id
                JOIN social_accounts sa ON sp.social_account_id = sa.id
                WHERE sp.tenant_id = :tenant_id
                AND sp.status IN ('posted', 'failed')
                ORDER BY sp.posted_at DESC, sp.scheduled_time_utc DESC
                LIMIT :limit";

        $stmt = $this->db->query($sql, ['tenant_id' => $this->getTenantId()]);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function updateStatus($scheduleId, $status, $logMessage = '') {
        $sql = "UPDATE scheduled_posts
                SET status = :status, log_message = :log_message, posted_at = NOW()
                WHERE id = :id";

        return $this->db->execute($sql, [
            'status' => $status,
            'log_message' => $logMessage,
            'id' => $scheduleId
        ]);
    }

    public function getCalendarView($startDate, $endDate) {
        $sql = "SELECT sp.*, ci.title, ci.type, sa.platform
                FROM scheduled_posts sp
                JOIN content_items ci ON sp.content_item_id = ci.id
                JOIN social_accounts sa ON sp.social_account_id = sa.id
                WHERE sp.tenant_id = :tenant_id
                AND sp.scheduled_time_utc BETWEEN :start_date AND :end_date
                ORDER BY sp.scheduled_time_utc ASC";

        return $this->db->fetchAll($sql, [
            'tenant_id' => $this->getTenantId(),
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
}
