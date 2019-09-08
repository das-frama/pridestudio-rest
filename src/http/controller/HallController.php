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
     * @method GET
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
     * @method GET
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

    /**
     * Get services from hall.
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function services(ServerRequestInterface $request): ResponseInterface
    {
        $slug = RequestUtils::getPathSegment($request, 2);
        if (!$this->service->isExists($slug)) {
            throw new ResourceNotFoundException("Hall not found.");
        }
        $params = $this->getQueryParams($request);
        $services = $this->service->findServices($slug, $params);

        return ResponseFactory::fromObject(200, $services);
    }
}
