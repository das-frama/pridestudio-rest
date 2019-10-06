<?php

declare(strict_types=1);

namespace app\domain\hall;

use app\entity\Hall;
use app\entity\Service;

interface HallRepositoryInterface
{
    /**
     * Find one hall by filter.
     * @param array $filter
     * @param array $include
     * @return Hall|null
     */
    public function findOne(array $filter, array $include = []): ?Hall;

    /**
     * Find hall's services.
     * @param array $filter
     * @param array $selected
     * @param array $include
     * @return Service[]
     */
    public function findServices(array $filter, array $selected, array $include = []): array;

    /**
     * Find all halls.
     * @param array $filter
     * @param array $include
     * @return Hall[]
     */
    public function findAll(array $filter, int $limit = 0, int $skip = 0, array $sort = [], array $include = []): array;

    /**
     * Search halls by regular expression.
     * @param array $filter
     * @param array $include
     * @return Hall[]
     */
    public function search(array $filter, int $limit = 0, int $skip = 0, array $sort = [], array $include = []): array;

    /**
     * Count halls by filter.
     * @return int
     */
    public function count(array $filter = []): int;

    /**
     * Insert a new hall into storage.
     * @param Hall $hall
     * @return string|null hall's id.
     */
    public function insert(Hall $hall): ?string;

    /**
     * Update an extisted hall from storage..
     * @param Hall $hall
     * @return bool.
     */
    public function update(Hall $hall): bool;

    /**
     * Check if hall exists by filter.
     * @param array $filter
     * @return bool
     */
    public function isExists(array $filter): bool;
    public function save(): bool;

    /**
     * @return bool
     */
    public function init(): bool;
}
