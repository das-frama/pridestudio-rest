<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\record\RecordService;
use app\http\exception\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Record class.
 */
class RecordController
{
    /**
     * @var RecordService
     */
    private $service;

    public function __construct(RecordService $service)
    {
        $this->service = $service;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $records = $this->service->findAll(0, 0);
        return ResponseFactory::fromObject(200, $records);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        $record = $this->service->findByID($id);
        if ($record === null) {
            throw new RouteNotFoundException();
        }

        return ResponseFactory::fromObject(200, $record);
    }
}
