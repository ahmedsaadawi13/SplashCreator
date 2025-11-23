<?php
// FILE: /app/models/AIGeneration.php

class AIGeneration extends Model {
    protected $table = null; // Will be set dynamically

    public function saveTextGeneration($data) {
        $this->table = 'ai_text_requests';
        return $this->create($data);
    }

    public function saveImageGeneration($data) {
        $this->table = 'ai_images';
        return $this->create($data);
    }

    public function saveVideoGeneration($data) {
        $this->table = 'ai_videos';
        return $this->create($data);
    }

    public function getTextGenerations($limit = 50) {
        $this->table = 'ai_text_requests';
        return $this->findAll([], 'created_at DESC', $limit);
    }

    public function getImageGenerations($limit = 50) {
        $this->table = 'ai_images';
        return $this->findAll([], 'created_at DESC', $limit);
    }

    public function getVideoGenerations($limit = 50) {
        $this->table = 'ai_videos';
        return $this->findAll([], 'created_at DESC', $limit);
    }

    public function getRecentGenerations($limit = 20) {
        $tenantId = $this->getTenantId();

        $sql = "
            SELECT 'text' as type, id, prompt as description, created_at FROM ai_text_requests WHERE tenant_id = :tenant_id
            UNION ALL
            SELECT 'image' as type, id, prompt as description, created_at FROM ai_images WHERE tenant_id = :tenant_id
            UNION ALL
            SELECT 'video' as type, id, script_text as description, created_at FROM ai_videos WHERE tenant_id = :tenant_id
            ORDER BY created_at DESC
            LIMIT :limit
        ";

        $stmt = $this->db->query($sql, ['tenant_id' => $tenantId]);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getGenerationStats() {
        $tenantId = $this->getTenantId();

        $sql = "SELECT
                (SELECT COUNT(*) FROM ai_text_requests WHERE tenant_id = :tenant_id) as text_count,
                (SELECT COUNT(*) FROM ai_images WHERE tenant_id = :tenant_id) as image_count,
                (SELECT COUNT(*) FROM ai_videos WHERE tenant_id = :tenant_id) as video_count,
                (SELECT SUM(tokens_count) FROM ai_text_requests WHERE tenant_id = :tenant_id) as total_tokens";

        return $this->db->fetch($sql, ['tenant_id' => $tenantId]);
    }
}
