<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Reservation;
use App\Repositories\Base\CommonRepositoryInterface;

/**
 * Interface RecordRepositoryInterface
 * @package App\Repositories
 */
interface RecordRepositoryInterface extends CommonRepositoryInterface
{
    /**
     * Find nested reservations in records.
     * @param array $filter
     * @return Reservation[]
     */
    public function findReservations(array $filter): array;
}
