<?php
declare(strict_types=1);

namespace App\Http\Controllers\Base;

use App\Http\Requests\Base\AbstractRequest;
use App\Http\Responders\ResponderInterface;
use App\Repositories\Base\ResourceRepositoryInterface;
use App\RequestUtils;
use App\ResponseFactory;
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
    protected string $requestClass;

    /**
     * ResourceController constructor.
     * @param string $entityClass
     * @param ResourceRepositoryInterface $repo
     * @param ResponderInterface $responder
     */
    public function __construct(
        string $entityClass,
        string $requestClass,
        ResourceRepositoryInterface $repo,
        ResponderInterface $responder
    ) {
        $this->entityClass = $entityClass;
        $this->requestClass = $requestClass;
        $this->repo = $repo;
        parent::__construct($responder);
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
        $records = $this->repo->findPaginated($pagination);
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
        $record = $this->repo->findOne(['id' => $id]);
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
        /** @var AbstractRequest $formRequest */
        $formRequest = new $this->requestClass($request);
        // Prepare.
        $record = new $this->entityClass($formRequest->toArray());
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
        /** @var AbstractRequest $formRequest */
        $formRequest = new $this->requestClass($request);
        // Prepare.
        $record = new $this->entityClass($formRequest->toArray());
        // Update.
        $id = RequestUtils::getPathSegment($request, 2);
        $record = $this->repo->findOneAndUpdate(['id' => $id], $record, true);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Record not found.');
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