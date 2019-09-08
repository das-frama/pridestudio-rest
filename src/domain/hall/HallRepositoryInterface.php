<?php

declare(strict_types=1);

namespace app\domain\hall;

use app\entity\Hall;
use app\entity\Service;

interface HallRepositoryInterface
{
    public function findOne(array $filter, bool $onlyActive = true, array $include = [], array $exclude = []): ?Hall;
    public function findWithServices(array $filter, bool $onlyActive = true, array $include = [], array $exclude = []): ?Hall;
    /**
     * @param array $filter
     * @param bool $onlyActive
     * @param array $include
     * @param array $exclude
     * @return Service[]
     */
    public function findServices(array $filter = [], bool $onlyActive = true, array $include = [], array $exclude = []): array;
    /**
     * @param int $limit
     * @param int $offset
     * @param bool $onlyActive
     * @param array $include
     * @param array $exclude
     * @return Hall[]
     */
    public function findAll(int $limit, int $offset, bool $onlyActive = true, array $include = [], array $exclude = []): array;
    public function isExists(array $filter, bool $onlyActive = true): bool;
    public function save(): bool;
}
