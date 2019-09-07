<?php

declare(strict_types=1);

namespace app\domain\record;

use app\entity\Hall;
use app\entity\Record;
use app\entity\Reservation;
use app\storage\mongodb\RecordRepository;

class RecordService
{
    /**
     * @var RecordRepositoryInterface
     */
    private $recordRepo;

    public function __construct(
        RecordRepository $recordRepo
    ) {
        $this->recordRepo = $recordRepo;
    }

    /**
     * Get record by id.
     * @param string $id
     * @return Record|null
     */
    public function findByID(string $id): ?Record
    {
        return $this->recordRepo->findByID($id);
    }

    /**
     * Get all records.
     * @param int $limit
     * @param int $offset
     * @return Record[]
     */
    public function findAll(int $limit, int $offset): array
    {
        return $this->recordRepo->findAll($limit, $offset);
    }

    /**
     * Calculate price for reservations.
     * @param Records $record
     * @return int
     */
    public function calculatePrice(Record $record): int
    {
        if ($record->hall === null || empty($record->reservations)) {
            return 0;
        }
        $amount = 0;
        $hours = 0;
        $calculdateBasePrice = empty($record->hall->prices);
        // Calculate reservations.
        foreach ($record->reservations as $reservation) {
            $hours += $reservation->length / 60;
            if ($calculdateBasePrice) {
                $amount += $hours * $record->hall->base_price;
            } else {
                // foreach ($record->hall->prices as $price) {
                //     $serviceIDs = array_intersect($record->service_ids, $record->hall->services);
                //     if ($record->service_ids) {

                //     }
                // }
            }
        }

        // Calculate services.
        foreach ($record->service_ids as $serviceID) { }

        return $amount;
    }
}
