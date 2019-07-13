<?php

declare(strict_types=1);

namespace app\storage;

use PDO;

/**
 * Interface Storage
 * @package app\storage
 */
interface StorageInterface
{
    public function getConnection(): PDO;
    public function findAll(string $table, int $limit, int $offset): array;
}
