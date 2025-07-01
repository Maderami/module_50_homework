<?php

namespace Core\Models;
use PDO;
class CommentModel
{
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getByImageId($imageId) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.username 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.image_id = ? 
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$imageId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($imageId, $userId, $text) {
        $stmt = $this->db->prepare("INSERT INTO comments (image_id, user_id, text) VALUES (?, ?, ?)");
        return $stmt->execute([$imageId, $userId, $text]);
    }

    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }
}