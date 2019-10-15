<?php

declare(strict_types=1);

namespace app\domain\file;

use app\entity\File;
use Psr\Http\Message\UploadedFileInterface;
use Imagick;

/**
 * Class FileService
 * @package app\domain\file
 */
class FileService
{
    /** @var string */
    private $storagePath;

    public function __construct(string $path)
    {
        $this->storagePath = $path;
    }

    /**
     * Get file by provided path.
     * @param string $path
     * @return File|null
     */
    public function findByPath(string $path): ?File
    {
        $filePath = $this->toOSPath($this->storagePath . '/' . $path);
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
        $uploadDir = $this->toOSPath($this->storagePath . '/' . $path);
        if (!is_dir(dirname($uploadDir))) {
            mkdir($uploadDir, 0777, true);
        }
        $name = $this->generateFileName($file);
        $file->moveTo($uploadDir . DIRECTORY_SEPARATOR . $name);
        $url = $this->storagePath . '/' . $path . '/' . $name;
        $url = str_replace('//', '/', $url);
        return $url;
    }

    /**
     * Remove file.
     * @param string $path
     * @return bool
     */
    public function remove(string $path): bool
    {
        $filePath = $this->toOSPath($path);
        if (!file_exists($filePath)) {
            return false;
        }
        return unlink($filePath);
    }

    public function createThumbnail(string $srcPath, string $destPath): bool
    {
        if (!extension_loaded('imagick')) {
            return false;
        }
        $osSrc = $this->toOSPath($srcPath);
        if (!file_exists($osSrc)) {
            return false;
        }
        $osDest = $this->toOSPath($destPath);
        if (!is_dir(dirname($osDest))) {
            return false;
        }


        $imagick = new Imagick($osSrc);
        $imagick->thumbnailImage(250, 0);
        $result = $imagick->writeImage($osDest);
        $imagick->destroy();

        return $result;
    }

    public function isImage(string $filePath): bool
    {
        $osPath = $this->toOSPath($filePath);
        if (!file_exists($osPath)) {
            return false;
        }
        $mimeType = mime_content_type($osPath);
        return substr($mimeType, 0, 5) === 'image';
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

    /**
     * Convert web path to os specific path.
     * @param string $path
     * @return string
     */
    private function toOSPath(string $path): string
    {
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        $path = str_replace('//', '/', $path);
        return WEB_ROOT_DIR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
