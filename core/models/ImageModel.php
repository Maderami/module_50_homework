<?php

namespace Core\Models;

use Core\Lib\FileUploader;
use PDO;

class ImageModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function upload($userId, $description = '')
    {
        $uploader = new FileUploader(
            UPLOAD_DIR,
            MAX_FILE_SIZE, // 10MB
            ALLOWED_TYPES
        );

        $result = $uploader->upload();

        if ($result) {
            $stmt = $this->db->prepare("INSERT INTO images (user_id, filename, original_name, description) VALUES (:user_id, :filename, :original_name, :description)");
            $stmt->execute([
                ':user_id' => $userId,
                ':filename' => $result['file_name'],
                ':original_name' => $result['original_name'],
                ':description' => $description
            ]);
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT id, filename FROM images ORDER BY uploaded_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM images WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($filename, $userId)
    {
        $stmt = $this->db->prepare("INSERT INTO images (filename, user_id) VALUES (?, ?)");
        return $stmt->execute([$filename, $userId]);
    }

    public function delete($id, $userId)
    {
        $stmt = $this->db->prepare("DELETE FROM images WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }
}