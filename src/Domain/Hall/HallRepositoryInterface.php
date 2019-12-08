<?php

declare(strict_types=1);

namespace App\Domain\Hall;

use App\Domain\CommonRepositoryInterface;
use App\Entity\Service;

interface HallRepositoryInterface extends CommonRepositoryInterface
{
    /**
     * Find hall's services.
     * @param array $filter
     * @param array $selected
     * @param array $include
     * @return Service[]
     */
    public function findServices(array $filter, array $selected, array $include = []): array;
}
