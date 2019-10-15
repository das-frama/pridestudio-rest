<?php

declare(strict_types=1);

namespace app\domain\record;

use app\domain\CommonRepositoryInterface;

interface RecordRepositoryInterface extends CommonRepositoryInterface
{
    /**
     * Find nested reservations in records.
     * @param array $filter
     * @return Reservation[]
     */
    public function findReservations(array $filter): array;
}
