<?php
declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Base\AbstractController;
use App\Http\Responders\ResponderInterface;
use App\Repositories\HallRepositoryInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\HallService;
use App\Services\ValidationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * HallController class.
 */
class HallController extends AbstractController
{
    protected HallService $service;
    protected HallRepositoryInterface $repo;

    /**
     * HallController constructor.
     * @param HallRepositoryInterface $repo
     * @param HallService $service
     * @param ResponderInterface $responder
     */
    public function __construct(HallRepositoryInterface $repo, HallService $service, ResponderInterface $responder)
    {
        parent::__construct($responder);
        $this->service = $service;
        $this->repo = $repo;
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
        $pagination = $this->getPagination($request);
        $halls = $this->repo->findPaginated($pagination);
        $count = $this->repo->count();
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
        $hall = $this->repo->findOne(['slug' => $slug]);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
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
        $id = RequestUtils::getPathSegment($request, 3);
        if (!$this->service->isExists($id)) {
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
        $services = $this->service->findServices($id, $selected, $params['include'] ?? []);
        return $this->responder->success($services, count($services));
    }
}
