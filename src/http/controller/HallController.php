<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\hall\HallService;
use app\domain\validation\ValidationService;
use app\http\controller\base\ControllerTrait;
use app\http\responder\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Hall class.
 */
class HallController
{
    use ControllerTrait;

    /** @var HallService */
    private $hallService;

    /** @var ResponderInterface */
    private $responder;

    /**
     * HallController constructor.
     * @param HallService $hallService
     * @param ResponderInterface $responder
     */
    public function __construct(HallService $hallService, ResponderInterface $responder)
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
        $include = $params['include'] ?? [];
        $halls = $this->hallService->findAll($params, $include);
        $count = isset($params['query']) ? count($halls) : $this->hallService->count();
        return $this->responder->success($halls, $count);
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
            return $this->responder->error(ResponseFactory::NOT_FOUND, ["Hall not found."]);
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
            return $this->responder->error(ResponseFactory::NOT_FOUND, ["Hall not found."]);
        }
        $params = $this->getQueryParams($request);
        $selected = [];
        if (isset($params['selected'])) {
            $selected = $params['selected'];
            $validationServices = new ValidationService;
            foreach ($selected as $id) {
                $err = $validationServices->validateMongoid($id);
                if ($err !== null) {
                    return $this->responder->error(ResponseFactory::BAD_REQUEST, ['Wrong id.']);
                }
            }
        }
        $services = $this->hallService->findServices($slug, $selected, $params['include'] ?? []);
        return $this->responder->success($services, count($services));
    }
}
