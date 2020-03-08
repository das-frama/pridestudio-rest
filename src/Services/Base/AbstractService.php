<?php
declare(strict_types=1);

namespace App\Services\Base;

use App\Entities\Base\AbstractEntity;
use App\Models\Pagination;
use App\Repositories\Base\CommonRepositoryInterface;

abstract class AbstractService
{
    protected CommonRepositoryInterface $repo;
    protected array $relations = [];

    /**
     * @param string $id
     * @param array $with
     * @return AbstractEntity|null
     */
    public function find(string $id, array $with = []): ?AbstractEntity
    {
        $record = $this->repo->findOne(['id' => $id]);
        if ($record === null) {
            return null;
        }
        foreach ($this->relations as $key => $relation) {
            /** @var $repository CommonRepositoryInterface */
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
     * @return AbstractEntity[]
     */
    public function all()
    {
        return $this->repo->findAll();
    }

    /**
     * Find paginated.
     *
     * @param Pagination $pagination
     * @param array $with
     * @return AbstractEntity[]
     */
    public function paginated(Pagination $pagination, array $with = [])
    {
        $records = $this->repo->findPaginated($pagination, [], $with);
        if ($with === []) {
            return $records;
        }

        foreach ($records as $record) {
            foreach ($this->relations as $key => $relation) {
                /** @var $repository CommonRepositoryInterface */
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

    public function create(AbstractEntity $entity)
    {
        return $this->repo->insert($entity);
    }

    public function update($id, array $input)
    {
        return $this->repo->update($id, $input);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function destroy(string $id): bool
    {
        return $this->repo->delete($id);
    }

    /**
     * Check if given record is exists.
     * @param string $id
     * @return bool
     */
    public function isExists(string $id): bool
    {
        return $this->repo->isExists(['id' => $id]);
    }

    /**
     * Count entities.
     * @return int
     */
    public function count(): int
    {
        return $this->repo->count();
    }
}
