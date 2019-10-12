<?php

declare(strict_types=1);

namespace app\domain\hall;

use app\domain\CommonRepositoryInterface;
use app\entity\Service;

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
