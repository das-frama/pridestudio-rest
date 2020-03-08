<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Service;
use App\Repositories\Base\CommonRepositoryInterface;

/**
 * Interface HallRepositoryInterface
 * @package App\Repositories
 */
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
