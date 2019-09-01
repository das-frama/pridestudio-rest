<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\Hall\HallService;
use app\http\exception\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Hall class.
 */
class HallController
{
    public $includeColumns = [];
    public $excludeColumns = ['created_by', 'updated_by', 'created_at', 'updated_at', 'is_active'];

    /**
     * @var HallService
     */
    private $service;


    public function __construct(HallService $service)
    {
        $this->service = $service;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $halls = $this->service->findAll(0, 0, $request->getQueryParams());
        return ResponseFactory::fromObject(200, $halls);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $slug = RequestUtils::getPathSegment($request, 2);
        $hall = $this->service->findBySlug($slug, $request->getQueryParams());
        if ($hall === null) {
            throw new RouteNotFoundException();
        }

        return ResponseFactory::fromObject(200, $hall);
    }
}