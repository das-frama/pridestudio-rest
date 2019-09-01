<?php

declare(strict_types=1);

namespace app\domain\hall;

use app\entity\Hall;

interface HallRepositoryInterface
{
    public function findByID(string $id): ?Hall;
    public function findBySlug(string $slug, bool $onlyActive, array $include): ?Hall;

    /**
     * @param int $limit
     * @param int $offset
     * @param bool $onlyActive
     * @param array $include
     * @return Hall[]
     */
    public function findAll(int $limit, int $offset, bool $onlyActive, array $include): array;
    public function save(): bool;
}
