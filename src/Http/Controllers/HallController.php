<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Hall;
use App\Http\Controllers\Base\AbstractController;
use App\Http\Responders\ResponderInterface;
use App\Http\ValidationRequests\Hall\FormValidationRequest;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\HallService;
use App\Services\ValidationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class HallController
 * @package App\Http\Controllers
 */
class HallController extends AbstractController
{
    protected HallService $service;

    /**
     * HallController constructor.
     * @param HallService $service
     * @param ResponderInterface $responder
     * @param ValidationService $validator
     */
    public function __construct(HallService $service, ResponderInterface $responder, ValidationService $validator)
    {
        parent::__construct($responder, $validator);
        $this->service = $service;
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
        $halls = $this->service->paginated($pagination);
        $count = $this->service->count();
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
        $id = RequestUtils::getPathSegment($request, 2);
        $hall = $this->service->find($id);
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
        $id = RequestUtils::getPathSegment($request, 2);
        if (!$this->service->isExists($id)) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }
        $selected = $this->getQueryParams($request, 'selected');
        // Validate selected if we have ones.
        // TODO (frama): Добавить валидацию objectId selected.
        // Fetch services.
        $services = $this->service->findServices($id, $selected);
        return $this->responder->success($services);
    }

    /**
     * Create a hall.
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $data = $this->validateRequest($request, new FormValidationRequest());

        // Prepare hall Entity.
        $hall = new Hall;
        $hall->load($data);

        // Create hall.
        $hall = $this->service->create($hall);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Error during saving a record.');
        }

        return $this->responder->success($hall);
    }

    /**
     * Update hall.
     * PUT /halls/<id>
     * @method PUT
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $data = $this->validateRequest($request, new FormValidationRequest());

        // Check if hall exists.
        $id = RequestUtils::getPathSegment($request, 2);
        $hall = $this->service->findByID($id);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }
        // Prepare hall for update.
        $hall->load($data);

        // Update hall.
        $hall = $this->service->update($hall);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Error during update.');
        }

        return $this->responder->success($hall);
    }

    /**
     * Delete hall.
     * DELETE /halls/<id>
     * @method DELETE
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        $isDeleted = $this->service->delete($id);
        return $this->responder->success($isDeleted, (int)$isDeleted);
    }
}
