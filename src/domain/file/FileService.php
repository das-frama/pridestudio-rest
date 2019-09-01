<?php

declare(strict_types=1);

namespace app\domain\file;

use app\entity\File;

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
}
