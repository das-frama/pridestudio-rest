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
    /** @var HallService */
    public $hallService;

    /**
     * HallController constructor.
     * @param HallService $hallService
     */
    public function __construct(HallService $hallService)
    {
        $this->hallService = $hallService;
    }

    /**
     * Get all halls.
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getQueryParams($request);
        $halls = $this->hallService->findAll($params['include'] ?? []);
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
        $params = $this->getQueryParams($request);
        $hall = $this->hallService->findBySlug($slug, $params['include'] ?? []);
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
        if (!$this->hallService->isExists($slug)) {
            throw new ResourceNotFoundException("Hall not found.");
        }
        $params = $this->getQueryParams($request);
        $services = $this->hallService->findServices(
            $slug,
            $params['selected'] ?? [],
            $params['include'] ?? []
        );
        return ResponseFactory::fromObject(200, $services);
    }
}
