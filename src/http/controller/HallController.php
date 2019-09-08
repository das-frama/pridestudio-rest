<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\hall\HallService;
use app\http\controller\base\Controller;
use app\http\exception\ResourceNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Hall class.
 */
class HallController extends Controller
{
    // public $includeColumns = [];
    // public $excludeColumns = ['created_by', 'updated_by', 'created_at', 'updated_at', 'is_active'];

    /** @var HallService */
    private $service;

    /**
     * HallController constructor.
     * @param HallService $service
     */
    public function __construct(HallService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all halls.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $halls = $this->service->findAll(0, 0, $this->getQueryParams($request));
        return ResponseFactory::fromObject(200, $halls);
    }

    /**
     * Get one hall by slug.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $slug = RequestUtils::getPathSegment($request, 2);
        $hall = $this->service->findBySlug($slug, $this->getQueryParams($request));
        if ($hall === null) {
            throw new ResourceNotFoundException("Hall not found.");
        }

        return ResponseFactory::fromObject(200, $hall);
    }

    public function services(ServerRequestInterface $request): ResponseInterface
    {
        $slug = RequestUtils::getPathSegment($request, 2);
        $params = $this->getQueryParams($request);
        $params['include'][] = 'services_object';
        $hall = $this->service->findBySlug($slug, $params);
        if ($hall === null) {
            throw new ResourceNotFoundException("Hall not found.");
        }

        return ResponseFactory::fromObject(200, $hall->services_object);
    }
}
