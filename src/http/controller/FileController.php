<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\file\FileService;
use app\http\responder\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * File class.
 */
class FileController
{
    /** @var FileService */
    private $fileService;

    /** @var ResponderInterface */
    private $responder;

    public function __construct(FileService $fileService, ResponderInterface $responder)
    {
        $this->fileService = $fileService;
        $this->responder = $responder;
    }

    /**
     * Get a file by path.
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $entity = RequestUtils::getPathSegment($request, 2);
        $name = RequestUtils::getPathSegment($request, 3);

        $file = $this->fileService->findByPath(join(DIRECTORY_SEPARATOR, [$entity, $name]));
        if ($file === null) {
            return ResponseFactory::fromStatus(ResponseFactory::NOT_FOUND);
        }

        return ResponseFactory::fromFile($file);
    }

    /**
     * Upload file.
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function upload(ServerRequestInterface $request): ResponseInterface
    {
        $files = $request->getUploadedFiles();
        $result = $this->fileService->uploadMany($files, '/uploaded');
        return $this->responder->success($result, count($result));
    }
}
