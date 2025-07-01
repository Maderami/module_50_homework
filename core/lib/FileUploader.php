<?php

namespace Core\Lib;

use finfo;

class FileUploader {
    private $uploadDir;
    private $maxFileSize;
    private $allowedTypes;
    private $errors = [];

    public function __construct($uploadDir, $maxFileSize, $allowedTypes) {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        $this->maxFileSize = $maxFileSize;
        $this->allowedTypes = $allowedTypes;

        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload() {
        if (!isset($_FILES['file'])) {
            $this->errors[] = 'Файл не был отправлен.';
            return false;
        }

        $file = $_FILES['file'];

        // Проверка ошибок
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadError($file['error']);
            return false;
        }

        // Проверка размера
        if ($file['size'] > $this->maxFileSize) {
            $this->errors[] = 'Файл слишком большой.';
            return false;
        }

        // Проверка типа
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $this->allowedTypes)) {
            $this->errors[] = 'Недопустимый тип файла.';
            return false;
        }

        // Генерация имени
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = $this->generateFilename($extension);
        $destination = $this->uploadDir . $newFilename;

        // Перемещение файла
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->errors[] = 'Не удалось сохранить файл.';
            return false;
        }

        return [
            'original_name' => $file['name'],
            'file_name'=> $newFilename,
            'stored_name' => $newFilename,
            'path' => $destination,
            'size' => $file['size'],
            'type' => $mime
        ];
    }

    public function getErrors() {
        return $this->errors;
    }

    private function generateFilename($extension) {
        return uniqid('file_', true) . '.' . $extension;
    }

    private function getUploadError($code) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Файл слишком большой.',
            UPLOAD_ERR_FORM_SIZE => 'Файл слишком большой.',
            UPLOAD_ERR_PARTIAL => 'Файл был загружен только частично.',
            UPLOAD_ERR_NO_FILE => 'Файл не был загружен.',
            UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка.',
            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск.',
            UPLOAD_ERR_EXTENSION => 'PHP-расширение остановило загрузку файла.'
        ];
        return $errors[$code] ?? 'Неизвестная ошибка загрузки.';
    }
}