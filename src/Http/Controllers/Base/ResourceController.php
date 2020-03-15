<?php
declare(strict_types=1);

namespace App\Http\Controllers\Base;

use App\Http\Responders\ResponderInterface;
use App\Repositories\Base\ResourceRepositoryInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\ValidationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ResourceController
 * @package App\Http\Controllers\Base
 */
class ResourceController extends AbstractController
{
    protected ResourceRepositoryInterface $repo;
    protected string $entityClass;
    protected array $validation = [
        'create' => [],
        'update' => [],
    ];
    protected array $with = [
        'all' => [],
        'read' => [],
//        'create' => [],
//        'update' => [],
    ];

    /**
     * ResourceController constructor.
     * @param string $entityClass
     * @param ResourceRepositoryInterface $repo
     * @param ResponderInterface $responder
     * @param ValidationService $validator
     */
    public function __construct(
        string $entityClass,
        ResourceRepositoryInterface $repo,
        ResponderInterface $responder,
        ValidationService $validator
    ) {
        $this->entityClass = $entityClass;
        $this->repo = $repo;
        parent::__construct($responder, $validator);
    }

    /**
     * Get all resources.
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $pagination = $this->getPagination($request);
        $records = $this->repo->findPaginated($pagination, [], $this->with['all']);
        return $this->responder->success($records);
    }

    /**
     * Get one record by id.
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        $record = $this->repo->findOne(['id' => $id], $this->with['read']);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Record not found.');
        }
        return $this->responder->success($record);
    }

    /**
     * Create a record.
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $data = $this->validateRequest($request, $this->validation['create']);

        // Prepare.
        $record = new $this->entityClass;
        $record->load($data);

        // Create.
        $record = $this->repo->insert($record);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Error during saving a record.');
        }

        return $this->responder->success($record);
    }

    /**
     * Update record.
     * @method PATCH
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $data = $this->validateRequest($request, $this->validation['update']);

        // Prepare.
        $record = new $this->entityClass;
        $record->load($data);

        // Update.
        $id = RequestUtils::getPathSegment($request, 2);
        $record = $this->repo->findOneAndUpdate(['id' => $id], $record, true);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Error during update.');
        }

        return $this->responder->success($record);
    }

    /**
     * Delete record.
     * @method DELETE
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function destroy(ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        $isDeleted = $this->repo->delete($id);
        return $this->responder->success($isDeleted, (int)$isDeleted);
    }
}