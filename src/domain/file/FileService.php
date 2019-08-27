<?php

declare(strict_types=1);

namespace app\domain\file;

use app\entity\File;

class FileService
{
    private $fileBasePath;

    public function __construct()
    {
        // $this->fileBasePath = $fileBasePath;
        $this->fileBasePath = ROOT_DIR . DIRECTORY_SEPARATOR . 'storage';
    }

    public function findByPath(string $path): ?File
    {
        $filePath = $this->fileBasePath . DIRECTORY_SEPARATOR . $path;
        if (!file_exists($filePath)) {
            return null;
        }
        $file = new File;
        $file->path = $filePath;
        // $file->url = HOST . str_replace(DIRECTORY_SEPARATOR, '/', $filePath);
        $file->mimeType = mime_content_type($filePath);

        return $file;
    }
}
