<?php

declare(strict_types=1);

namespace app\domain\record;

use app\entity\Record;

interface RecordRepositoryInterface
{
    /**
     * Find record by id.
     * @param array $filter
     * @param array $include
     * @return Record|null
     */
    public function findOne(array $filter, array $include = []): ?Record;

    /**
     * Find all records by filter.
     * @param array $filter
     * @param array $include
     * @return Record[]
     */
    public function findAll(array $filter = [], array $include = []): array;
    public function save(): bool;
}
