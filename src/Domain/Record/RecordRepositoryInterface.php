<?php

declare(strict_types=1);

namespace App\Domain\Record;

use App\Domain\CommonRepositoryInterface;

interface RecordRepositoryInterface extends CommonRepositoryInterface
{
    /**
     * Find nested reservations in records.
     * @param array $filter
     * @return Reservation[]
     */
    public function findReservations(array $filter): array;
}
