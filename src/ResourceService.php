<?php

declare(strict_types=1);

namespace app;

use app\storage\StorageInterface;

class ResourceService
{
    private $db;

    public function __construct(StorageInterface $db)
    {
        $this->db = $db;
    }

    public function list(string $table): array
    {
        return $this->db->findAll($table, 0, 0);
    }
}
