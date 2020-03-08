<?php
declare(strict_types=1);

namespace App\Models;

class Pagination
{
    public int $page = 1;
    public string $query = '';
    public int $limit = 15;
    public string $orderBy = '';
    public int $ascending = 0;

    /**
     * @return int
     */
    public function skip(): int
    {
        return $this->limit * ($this->page - 1);
    }
}
