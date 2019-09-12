<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\domain\hall\HallService;
use app\http\controller\base\ControllerTrait;
use app\http\exception\ResourceNotFoundException;
use app\http\responder\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Hall class.
 */
class HallController
{
    use ControllerTrait;

    /** @var HallService */
    public $hallService;

    /**
     * HallController constructor.
     * @param HallService $hallService
     * @param JsonResponder $responder
     */
    public function __construct(HallService $hallService, JsonResponder $responder)
    {
        $this->hallService = $hallService;
        $this->responder = $responder;
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
        return $this->responder->success($halls);
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
        return $this->responder->success($hall);
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
        return $this->responder->success($services);
    }
}
