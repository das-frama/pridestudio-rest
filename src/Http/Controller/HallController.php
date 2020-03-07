<?php
declare(strict_types=1);

namespace App\Http\Controller;

use App\Domain\Hall\HallService;
use App\Domain\Validation\ValidationService;
use App\Entity\Hall;
use App\Http\Controller\Base\AbstractController;
use App\Http\Responder\ResponderInterface;
use App\Http\ValidationRequest\Hall\FormValidationRequest;
use App\RequestUtils;
use App\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class HallController
 * @package App\Http\Controller
 */
class HallController extends AbstractController
{
    protected HallService $hallService;

    /**
     * HallController constructor.
     * @param HallService $hallService
     * @param ResponderInterface $responder
     * @param ValidationService $validator
     */
    public function __construct(HallService $hallService, ResponderInterface $responder, ValidationService $validator)
    {
        parent::__construct($responder, $validator);
        $this->hallService = $hallService;
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
        $halls = $this->hallService->findAll($pagination);
        $count = $this->hallService->count();
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
        $hall = $this->hallService->findByID($id);
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
        if (!$this->hallService->isExists($id)) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }
        $selected = $this->getQueryParams($request, 'selected');
        // Validate selected if we have ones.
        // TODO (frama): Добавить валидацию objectId selected.
        // Fetch services.
        $services = $this->hallService->findServices($id, $selected);
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
        $hall = $this->hallService->create($hall);
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
        $hall = $this->hallService->findByID($id);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }
        // Prepare hall for update.
        $hall->load($data);

        // Update hall.
        $hall = $this->hallService->update($hall);
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
        $isDeleted = $this->hallService->delete($id);
        return $this->responder->success($isDeleted, (int)$isDeleted);
    }
}
