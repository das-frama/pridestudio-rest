<?php
declare(strict_types=1);

namespace App\Model;

class Pagination
{
    public int $page = 1;
    public string $query = '';
    public int $limit = 15;
    public string $orderBy = '';
    public int $ascending = 0;
}