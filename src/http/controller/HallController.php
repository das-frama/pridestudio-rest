<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\entity\Hall;
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
     * GET /halls
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getQueryParams($request);
        $include = $params['include'] ?? [];
        $halls = $this->hallService->findAll($params, false, $include);
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
        $id = RequestUtils::getPathSegment($request, 2);
        $params = $this->getQueryParams($request);
        $hall = $this->hallService->findByID($id, $params['include'] ?? []);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ["Hall not found."]);
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
        $id = RequestUtils::getPathSegment($request, 2);
        if (!$this->hallService->isExists($id)) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ["Hall not found."]);
        }
        $params = $this->getQueryParams($request);
        $selected = $params['selected'] ?? [];
        if (!empty($selected)) {
            $validationServices = new ValidationService;
            foreach ($selected as $selectedID) {
                $err = $validationServices->validateObjectId($selectedID);
                if ($err !== []) {
                    return $this->responder->error(ResponseFactory::BAD_REQUEST, $err);
                }
            }
        }
        $services = $this->hallService->findServices($id, $selected, $params['include'] ?? []);
        return $this->responder->success($services, count($services));
    }

    /**
     * Create a hall.
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        // Validate body from request.
        $data = $request->getParsedBody();
        if (empty($data)) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ['Empty body.']);
        }
        $errors = (new ValidationService)->validate($data, [
            'services' => ['array:0:50'],
            'services.$.category_id' => ['object_id'],
            'services.$.children' => ['array:0:16'],
            'services.$.children.$' => ['object_id'],
            'services.$.parents' => ['array:0:16'],
            'services.$.parents.$' => ['object_id'],
            'prices' => ['array:0:50'],
            'prices.$.time_from' => ['time'],
            'prices.$.time_to' => ['time'],
            'prices.$.type' => ['enum:1,2'],
            'prices.$.from_length' => ['int:60:1440'],
            'prices.$.comparison' => ['enum:>,>=,<,<=,=,!='],
            'prices.$.price' => ['int:0:9999999'],
            'prices.$.service_ids' => ['array:0:16'],
            'prices.$.service_ids.$' => ['object_id'],
            'name' => ['required', 'string:1:64'],
            'slug' => ['required', 'string:1:64'],
            'preview_image' => ['string:1:255'],
            'base_price' => ['int:0:999999'],
            'sort' => ['int'],
            'is_active' => ['bool'],
        ]);
        if ($errors !== []) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, $errors);
        }

        // Prepare hall entity.
        $hall = new Hall;
        $hall->load($data, [
            'name', 'slug', 'description', 'preview_image', 'base_price', 'services', 'prices', 'sort', 'is_active',
        ]);
    
        // Create hall.
        $hall = $this->hallService->create($hall);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, ['Error during saving a record.']);
        }

        return $this->responder->success($hall, 1);
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
        // Check if hall exists.
        $id = RequestUtils::getPathSegment($request, 2);
        $hall = $this->hallService->findByID($id);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ["Hall not found."]);
        }

        // Get body from request.
        $data = $request->getParsedBody();
        if (empty($data)) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ['Empty body.']);
        }

        // Validate data.
        $errors = (new ValidationService)->validate($data, [
            'services' => ['array:0:50'],
            'services.$.category_id' => ['object_id'],
            'services.$.children' => ['array:0:16'],
            'services.$.children.$' => ['object_id'],
            'services.$.parents' => ['array:0:16'],
            'services.$.parents.$' => ['object_id'],
            'prices' => ['array:0:50'],
            'prices.$.time_from' => ['time'],
            'prices.$.time_to' => ['time'],
            'prices.$.type' => ['enum:1,2'],
            'prices.$.from_length' => ['int:60:1440'],
            'prices.$.comparison' => ['enum:>,>=,<,<=,=,!='],
            'prices.$.price' => ['int:0:9999999'],
            'prices.$.service_ids' => ['array:0:16'],
            'prices.$.service_ids.$' => ['object_id'],
            'name' => ['required', 'string:1:64'],
            'slug' => ['required', 'string:1:64'],
            'preview_image' => ['string:1:255'],
            'base_price' => ['int:0:999999'],
            'sort' => ['int'],
            'is_active' => ['bool'],
        ]);
        if ($errors !== []) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, $errors);
        }

        // Prepare hall for update.
        $hall->load($data, [
            'name', 'slug', 'description', 'base_price', 'preview_image', 'services', 'prices', 'sort', 'is_active'
        ]);

        // Update hall.
        $hall = $this->hallService->update($hall);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, ['Error during update.']);
        }

        return $this->responder->success($hall, 1);
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
        return $this->responder->success($isDeleted, (int) $isDeleted);
    }
}
