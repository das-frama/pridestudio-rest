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
     * @param Hall $hall
     * @param Reservation[] $reservations
     * @return int
     */
    public function calculatePrice(Hall $hall, array $reservations): int
    {
        $amount = 0;

        // Calculate services.
        // foreach ($hall->services as $category) {
        //     foreach ($category->children as $service) {
        //         $service->name 
        //     }
        //  }

        // Calculate reservations.
        foreach ($reservations as $reservation) {
            $amount += ($reservation->length / 60) * $hall->base_price;
        }

        return $amount;
    }
}
