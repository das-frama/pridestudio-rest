<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\file\FileService;
use app\http\exception\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * File class.
 */
class FileController
{
    /** @var FileService */
    public $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Get a file by path.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $type = RequestUtils::getPathSegment($request, 2);
        $entity = RequestUtils::getPathSegment($request, 3);
        $name = RequestUtils::getPathSegment($request, 4);

        $file = $this->fileService->findByPath(join(DIRECTORY_SEPARATOR, [$type, $entity, $name]));
        if ($file === null) {
            throw new RouteNotFoundException();
        }

        return ResponseFactory::fromFile($file);
    }
}
