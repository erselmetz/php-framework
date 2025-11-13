<?php

namespace Core;

class Upload
{
    private $file;
    private $allowedExtensions = [];
    private $maxSize = 2097152; // 2MB default
    private $uploadPath = null;

    public function __construct(string $fieldName)
    {
        if (!isset($_FILES[$fieldName])) {
            throw new \Exception("File field '{$fieldName}' not found in upload.");
        }

        if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("Upload error: " . $this->getErrorMessage($_FILES[$fieldName]['error']));
        }

        $this->file = $_FILES[$fieldName];
    }

    public static function make(string $fieldName): self
    {
        return new self($fieldName);
    }

    public function allowed(array $extensions): self
    {
        $this->allowedExtensions = array_map('strtolower', $extensions);
        return $this;
    }

    public function maxSize(int $bytes): self
    {
        $this->maxSize = $bytes;
        return $this;
    }

    public function path(string $path): self
    {
        $this->uploadPath = rtrim($path, '/') . '/';
        return $this;
    }

    public function store(string $filename = null): string
    {
        if ($this->uploadPath === null) {
            $this->uploadPath = dirname(__DIR__) . '/storage/uploads/';
        }

        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }

        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));

        if (!empty($this->allowedExtensions) && !in_array($extension, $this->allowedExtensions)) {
            throw new \Exception("File extension '{$extension}' is not allowed.");
        }

        if ($this->file['size'] > $this->maxSize) {
            throw new \Exception("File size exceeds maximum allowed size of " . $this->formatBytes($this->maxSize));
        }

        $filename = $filename ?? uniqid() . '_' . time() . '.' . $extension;
        $destination = $this->uploadPath . $filename;

        if (!move_uploaded_file($this->file['tmp_name'], $destination)) {
            throw new \Exception("Failed to move uploaded file.");
        }

        return $filename;
    }

    public function getOriginalName(): string
    {
        return $this->file['name'];
    }

    public function getSize(): int
    {
        return $this->file['size'];
    }

    public function getMimeType(): string
    {
        return $this->file['type'];
    }

    private function getErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        ];

        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

