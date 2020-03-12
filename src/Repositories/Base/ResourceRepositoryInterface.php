<?php
declare(strict_types=1);

namespace App\Repositories\Base;

use App\Entities\Base\AbstractEntity;
use App\Models\Pagination;

/**
 * Interface CommonRepositoryInterface
 * @package App\Repositories\Base
 */
interface ResourceRepositoryInterface
{
    /**
     * Find one Entity by filter.
     * @param array $filter
     * @param array $with
     * @return AbstractEntity|null
     */
    public function findOne(array $filter, array $with = []): ?AbstractEntity;

    /**
     * Find one Entity and update by filter.
     * @param array $filter
     * @param AbstractEntity $entity
     * @param bool $returnNew
     * @return AbstractEntity|null
     */
    public function findOneAndUpdate(array $filter, AbstractEntity $entity, bool $returnNew = false): ?AbstractEntity;

    /**
     * Find all entities.
     * @param array $filter
     * @param array $with
     * @return AbstractEntity[]
     */
    public function findAll(array $filter = [], array $with = []): array;

    /**
     * Search entities by regular expression.
     * @param Pagination $pagination
     * @param array $filter
     * @param array $with
     * @return AbstractEntity[]
     */
    public function findPaginated(Pagination $pagination, array $filter = [], array $with = []): array;

    /**
     * Count entities by filter.
     * @param array $filter
     * @return int
     */
    public function count(array $filter = []): int;

    /**
     * Insert a new Entity into storage.
     * @param AbstractEntity $entity
     * @return AbstractEntity|null
     */
    public function insert(AbstractEntity $entity): ?AbstractEntity;

    /**
     * Update an existed Entity from storage.
     * @param AbstractEntity $entity
     * @param bool $upsert
     * @return AbstractEntity|null
     */
    public function update(AbstractEntity $entity, bool $upsert = false): ?AbstractEntity;

    /**
     * Delete an existing Entity from storage..
     * @param string $id
     * @return bool.
     */
    public function delete(string $id): bool;

    /**
     * Check if Entity exists by filter.
     * @param array $filter
     * @return bool
     */
    public function isExists(array $filter): bool;

    /**
     * Init collection schema validation and other stuff.
     * @return bool
     */
    public function init(): bool;
}
