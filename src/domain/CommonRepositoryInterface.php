<?php

declare(strict_types=1);

namespace app\domain;

use app\storage\mongodb\base\AbstractEntity;

interface CommonRepositoryInterface
{
    /**
     * Find one entity by filter.
     * @param array $filter
     * @param array $include
     * @return AbstractEntity|null
     */
    public function findOne(array $filter, array $include = []): ?AbstractEntity;

    /**
     * Find all entities.
     * @param array $filter
     * @param array $include
     * @return AbstractEntity[]
     */
    public function findAll(array $filter, int $limit = 0, int $skip = 0, array $sort = [], array $include = []): array;

    /**
     * Search entities by regular expression.
     * @param array $filter
     * @param array $include
     * @return AbstractEntity[]
     */
    public function search(array $search, int $limit = 0, int $skip = 0, array $sort = [], array $include = []): array;
    
    /**
     * Count enities by filter.
     * @return int
     */
    public function count(array $filter = []): int;

    /**
     * Insert a new entity into storage.
     * @param AbstractEntity $entity
     * @return string|null entity's id.
     */
    public function insert(AbstractEntity $entity): ?string;

    /**
     * Update an extisted entity from storage..
     * @param AbstractEntity $entity
     * @return bool.
     */
    public function update(AbstractEntity $entity): bool;

    /**
     * Delet an extisting entity from storage..
     * @param string $id
     * @return bool.
     */
    public function delete(string $id): bool;

    /**
     * Check if entity exists by filter.
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
