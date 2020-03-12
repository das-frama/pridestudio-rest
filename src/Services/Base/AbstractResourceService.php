<?php
declare(strict_types=1);

namespace App\Services\Base;

use App\Entities\Base\AbstractEntity;
use App\Models\Pagination;
use App\Repositories\Base\ResourceRepositoryInterface;

abstract class AbstractResourceService implements ResourceServiceInterface
{
    protected ResourceRepositoryInterface $repo;
    protected array $relations = [];

    /**
     * @inheritDoc
     */
    public function find(string $id, array $with = []): ?AbstractEntity
    {
        $record = $this->repo->findOne(['id' => $id]);
        if ($record === null) {
            return null;
        }
        foreach ($this->relations as $key => $relation) {
            /** @var $repository ResourceRepositoryInterface */
            list ($prop, $repository) = $relation;
            if (in_array($key, $with) && property_exists($record, $key) && isset($record->{$prop})) {
                if (is_array($record->{$prop})) {
                    $record->{$key} = $repository->findAll(['id' => $record->{$prop}]);
                } else {
                    $record->{$key} = $repository->findOne(['id' => $record->{$prop}]);
                }
            }
        }

        return $record;
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->repo->findAll();
    }

    /**
     * @inheritDoc
     */
    public function paginated(Pagination $pagination, array $with = []): array
    {
        $records = $this->repo->findPaginated($pagination, [], $with);
        if ($with === []) {
            return $records;
        }

        foreach ($records as $record) {
            foreach ($this->relations as $key => $relation) {
                /** @var $repository ResourceRepositoryInterface */
                list ($prop, $repository) = $relation;
                if (in_array($key, $with) && property_exists($record, $key) && isset($record->{$prop})) {
                    if (is_array($record->{$prop})) {
                        $record->{$key} = $repository->findAll(['id' => $record->{$prop}]);
                    } else {
                        $record->{$key} = $repository->findOne(['id' => $record->{$prop}]);
                    }
                }
            }
        }
        return $records;
    }

    /**
     * @inheritDoc
     */
    public function create(AbstractEntity $entity): ?AbstractEntity
    {
        return $this->repo->insert($entity);
    }

    /**
     * @inheritDoc
     */
    public function update(AbstractEntity $entity): ?AbstractEntity
    {
        return $this->repo->update($entity);
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $id): bool
    {
        return $this->repo->delete($id);
    }

    /**
     * @inheritDoc
     */
    public function isExists(string $id): bool
    {
        return $this->repo->isExists(['id' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->repo->count();
    }
}
