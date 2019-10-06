<?php

declare(strict_types=1);

namespace app\domain\file;

use app\entity\File;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class FileService
 * @package app\domain\file
 */
class FileService
{
    private $fileBasePath;

    public function __construct()
    {
        $this->fileBasePath = ROOT_DIR . DIRECTORY_SEPARATOR . 'storage';
    }

    /**
     * Get file by provided path.
     * @param string $path
     * @return File|null
     */
    public function findByPath(string $path): ?File
    {
        $filePath = $this->fileBasePath . DIRECTORY_SEPARATOR . $path;
        if (!file_exists($filePath)) {
            return null;
        }
        $file = new File;
        $file->path = $filePath;
        $file->mimeType = mime_content_type($filePath);

        return $file;
    }

    /**
     * Upload many files.
     * @param UploadedFileInterface[] $files
     * @param string $path
     * @return string[]|null
     */
    public function uploadMany(array $files, string $path): ?array
    {
        return array_map(function (UploadedFileInterface $file) use ($path) {
            return $this->upload($file, $path);
        }, $files);
    }

    /**
     * Upload one file.
     * @param UploadedFileInterface $file
     * @param string $path
     * @return string|null
     */
    public function upload(UploadedFileInterface $file, string $path): ?string
    {
        $name = $this->generateFileName($file);
        $uploadDir = join(DIRECTORY_SEPARATOR, [$this->fileBasePath, substr($path, 1)]);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir);
        }
        $file->moveTo($uploadDir . DIRECTORY_SEPARATOR . $name);
        return $path . '/' . $name;
    }

    /**
     * Generate new file name.
     * @param UploadedFileInterface $file
     * @return string
     */
    private function generateFileName(UploadedFileInterface $file): string
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];
        $mimeType = $file->getClientMediaType();
        $ext = $extensions[$mimeType] ?? '';
        return uniqid() . '.' . $ext;
    }
}
