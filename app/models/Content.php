<?php
// FILE: /app/models/Content.php

class Content extends Model {
    protected $table = 'content_items';

    public function getContentByType($type, $limit = 50, $offset = 0) {
        return $this->findAll(['type' => $type], 'created_at DESC', $limit, $offset);
    }

    public function searchContent($searchTerm, $type = null) {
        $sql = "SELECT * FROM content_items WHERE tenant_id = :tenant_id";
        $params = ['tenant_id' => $this->getTenantId()];

        $sql .= " AND (title LIKE :search OR content_text LIKE :search)";
        $params['search'] = "%{$searchTerm}%";

        if ($type) {
            $sql .= " AND type = :type";
            $params['type'] = $type;
        }

        $sql .= " ORDER BY created_at DESC LIMIT 100";

        return $this->db->fetchAll($sql, $params);
    }

    public function getWithUser($contentId) {
        $sql = "SELECT ci.*, u.full_name as creator_name, u.email as creator_email
                FROM content_items ci
                JOIN users u ON ci.user_id = u.id
                WHERE ci.id = :id AND ci.tenant_id = :tenant_id
                LIMIT 1";

        return $this->db->fetch($sql, [
            'id' => $contentId,
            'tenant_id' => $this->getTenantId()
        ]);
    }

    public function getRecentContent($limit = 10) {
        return $this->findAll([], 'created_at DESC', $limit);
    }

    public function addComment($contentId, $userId, $commentText) {
        $sql = "INSERT INTO content_comments (tenant_id, content_item_id, user_id, comment_text)
                VALUES (:tenant_id, :content_item_id, :user_id, :comment_text)";

        return $this->db->execute($sql, [
            'tenant_id' => $this->getTenantId(),
            'content_item_id' => $contentId,
            'user_id' => $userId,
            'comment_text' => $commentText
        ]);
    }

    public function getComments($contentId) {
        $sql = "SELECT cc.*, u.full_name, u.email
                FROM content_comments cc
                JOIN users u ON cc.user_id = u.id
                WHERE cc.content_item_id = :content_item_id
                AND cc.tenant_id = :tenant_id
                ORDER BY cc.created_at DESC";

        return $this->db->fetchAll($sql, [
            'content_item_id' => $contentId,
            'tenant_id' => $this->getTenantId()
        ]);
    }
}
