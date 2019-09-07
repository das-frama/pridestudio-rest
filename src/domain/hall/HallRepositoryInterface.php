<?php

declare(strict_types=1);

namespace app\domain\hall;

use app\entity\Hall;

interface HallRepositoryInterface
{
    public function findByID(string $id, bool $onlyActive = true, array $include = [], array $exclude = []): ?Hall;
    public function findBySlug(string $slug, bool $onlyActive = true, array $include = [], array $exclude = []): ?Hall;
    public function findWithServices(array $match = []): ?Hall;

    /**
     * @param int $limit
     * @param int $offset
     * @param bool $onlyActive
     * @param array $include
     * @param array $exclude
     * @return Hall[]
     */
    public function findAll(int $limit, int $offset, bool $onlyActive = true, array $include = [], array $exclude = []): array;
    public function save(): bool;
}
