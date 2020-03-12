<?php

namespace App\Services\Base;

use App\Entities\Base\AbstractEntity;
use App\Models\Pagination;

interface ResourceServiceInterface
{
    /**
     * @param string $id
     * @param array $with
     * @return AbstractEntity|null
     */
    public function find(string $id, array $with = []): ?AbstractEntity;

    /**
     * @return AbstractEntity[]
     */
    public function all(): array;

    /**
     * Find paginated.
     *
     * @param Pagination $pagination
     * @param array $with
     * @return AbstractEntity[]
     */
    public function paginated(Pagination $pagination, array $with = []): array;

    /**
     * @param AbstractEntity $entity
     * @return AbstractEntity|null
     */
    public function create(AbstractEntity $entity): ?AbstractEntity;

    /**
     * @param AbstractEntity $entity
     * @return AbstractEntity|null
     */
    public function update(AbstractEntity $entity): ?AbstractEntity;

    /**
     * @param string $id
     * @return bool
     */
    public function destroy(string $id): bool;

    /**
     * Check if given record is exists.
     * @param string $id
     * @return bool
     */
    public function isExists(string $id): bool;

    /**
     * Count entities.
     * @return int
     */
    public function count(): int;
}