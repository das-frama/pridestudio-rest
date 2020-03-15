<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Base\ControllerTrait;
use App\Http\Responders\ResponderInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\HallService;
use App\Services\ValidationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * HallController class.
 */
class HallController
{
    use ControllerTrait;

    private HallService $hallService;
    private ResponderInterface $responder;

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
     * GET /halls
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getQueryParams($request);
        $include = $params['include'] ?? [];
        $halls = $this->hallService->findAll($params, true, $include);
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
        $slug = RequestUtils::getPathSegment($request, 3);
        $params = $this->getQueryParams($request);
        $hall = $this->hallService->findBySlug($slug, $params['include'] ?? []);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }
        return $this->responder->success($hall, 1);
    }

    /**
     * Get services from hall.
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function services(ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 3);
        if (!$this->hallService->isExists($id)) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }
        $params = $this->getQueryParams($request);
        $selected = $params['selected'] ?? [];
        if (!empty($selected)) {
            $validationServices = new ValidationService;
            foreach ($selected as $selectedID) {
                $err = $validationServices->validateObjectId($selectedID);
                if ($err !== []) {
                    return $this->responder->error(ResponseFactory::BAD_REQUEST, 'Bad request.', $err);
                }
            }
        }
        $services = $this->hallService->findServices($id, $selected, $params['include'] ?? []);
        return $this->responder->success($services, count($services));
    }
}
