<?php

declare(strict_types=1);

namespace App\Domain;

use App\Storage\MongoDB\Base\AbstractEntity;

interface CommonRepositoryInterface
{
    /**
     * Find one Entity by filter.
     * @param array $filter
     * @param array $include
     * @return AbstractEntity|null
     */
    public function findOne(array $filter, array $include = []): ?AbstractEntity;

    /**
     * Find one Entity and update by filter.
     * @param array $filter
     * @param AbstractEntity $Entity
     * @param array $include
     * @param bool $returnNew
     * @return AbstractEntity|null
     */
    public function findOneAndUpdate(
        array $filter,
        AbstractEntity $Entity,
        array $include = [],
        bool $returnNew = false
    ): ?AbstractEntity;

    /**
     * Find all entities.
     * @param array $filter
     * @param int $limit
     * @param int $skip
     * @param array $sort
     * @param array $include
     * @return AbstractEntity[]
     */
    public function findAll(array $filter, int $limit = 0, int $skip = 0, array $sort = [], array $include = []): array;

    /**
     * Search entities by regular expression.
     * @param array $search
     * @param int $limit
     * @param int $skip
     * @param array $sort
     * @param array $include
     * @return AbstractEntity[]
     */
    public function search(array $search, int $limit = 0, int $skip = 0, array $sort = [], array $include = []): array;

    /**
     * Count enities by filter.
     * @param array $filter
     * @return int
     */
    public function count(array $filter = []): int;

    /**
     * Insert a new Entity into storage.
     * @param AbstractEntity $Entity
     * @return AbstractEntity|null
     */
    public function insert(AbstractEntity $Entity): ?AbstractEntity;

    /**
     * Update an extisted Entity from storage.
     * @param AbstractEntity $Entity
     * @param bool $upsert
     * @return AbstractEntity|null
     */
    public function update(AbstractEntity $Entity, bool $upsert = false): ?AbstractEntity;

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
