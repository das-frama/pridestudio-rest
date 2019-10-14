<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\hall\HallService;
use app\domain\validation\ValidationService;
use app\entity\Hall;
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
        $id = RequestUtils::getPathSegment($request, 2);
        $params = $this->getQueryParams($request);
        $err = (new ValidationService)->validateMongoid($id);
        if ($err === null) {
            $hall = $this->hallService->findByID($id, $params['include'] ?? []);
        } else {
            $hall = $this->hallService->findBySlug($id, $params['include'] ?? []);
        }
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
                $err = $validationServices->validateMongoid($selectedID);
                if ($err !== null) {
                    return $this->responder->error(ResponseFactory::BAD_REQUEST, ['Wrong id.']);
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
        // Get body from request.
        $body = $request->getParsedBody();
        if ($body === null) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ['Empty body.']);
        }
        $validationService = new ValidationService;
        $rules = [
            'name' => ['required', 'string:1:64'],
            'slug' => ['required', 'string:1:64'],
            'preview_image' => ['string:1:255'],
            'base_price' => ['int:0:999999'],
            'sort' => ['int'],
            'is_active' => ['bool'],
        ];
        // Sanitize incoming data.
        $body = $validationService->sanitize($body, $rules);
        // Validate data.
        $errors = $validationService->validate($body, $rules);
        if ($errors !== []) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, $errors);
        }
        // Prepare hall entity.
        $hall = new Hall;
        $hall->name = $body->name;
        $hall->slug = $body->slug;
        $hall->preview_image = $body->preview_image;
        $hall->base_price = (int) $body->base_price;
        $hall->sort = (int) $body->sort;
        $hall->is_active = (bool) $body->is_active;

        // Create hall.
        $id = $this->hallService->create($hall);
        if ($id === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, ['Error during saving a record.']);
        }

        return $this->responder->success($id);
    }

    /**
     * Create a hall.
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        // Find hall.
        $hall = $this->hallService->findByID($id);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ["Hall not found."]);
        }
        // Get body from request.
        $body = $request->getParsedBody();
        if ($body === null) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ['Empty body.']);
        }
        $validationService = new ValidationService;
        $rules = [
            'name' => ['required', 'string:1:64'],
            'slug' => ['required', 'string:1:64'],
            'description' => ['string:1:1024'],
            'preview_image' => ['string:1:255'],
            'base_price' => ['int:0:999999'],
            'sort' => ['int'],
            'is_active' => ['bool'],
        ];
        // Sanitize incoming data.
        $body = $validationService->sanitize($body, $rules);
        // Validate data.
        $errors = $validationService->validate($body, $rules);
        if ($errors !== []) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, $errors);
        }
        // Prepare hall entity.
        $hall->name = $body->name;
        $hall->slug = $body->slug;
        $hall->description = $body->description;
        $hall->preview_image = $body->preview_image;
        $hall->base_price = (int) $body->base_price;
        $hall->sort = (int) $body->sort;
        $hall->is_active = (bool) $body->is_active;

        // Update hall.
        $err = $this->hallService->update($hall);
        if ($err !== null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, [$err]);
        }

        return $this->responder->success(true, 1);
    }

    /**
     * Delete hall.
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
