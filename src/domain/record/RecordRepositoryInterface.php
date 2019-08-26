<?php

declare(strict_types=1);

namespace app\domain\record;

use app\entity\Record;

interface RecordRepositoryInterface
{
    public function findByID(string $id): ?Record;

    /**
     * @param int $limit
     * @param int $offset
     * @return Record[]
     */
    public function findAll(int $limit, int $offset): array;
    public function save(): bool;
}
