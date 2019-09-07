<?php

declare(strict_types=1);

namespace app\domain\record;

use app\entity\PriceRule;
use app\entity\Record;
use app\entity\Reservation;
use app\storage\mongodb\RecordRepository;
use DateTimeImmutable;

class RecordService
{
    /** @var RecordRepositoryInterface */
    private $recordRepo;

    public function __construct(RecordRepository $recordRepo)
    {
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
        $totalLength = 0;
        $calculdateBasePrice = empty($record->hall->prices);

        // Calculate reservations.
        foreach ($record->reservations as $reservation) {
            $totalLength += $reservation->length;
            if ($calculdateBasePrice) {
                $amount += $record->hall->base_price * intval($reservation->length / 60);
                continue;
            }

            foreach ($record->hall->prices as $price) {
                $serviceIDs = array_intersect($record->service_ids, $price['service_ids']);
                if (empty($serviceIDs)) {
                    continue;
                }
                $amount += $this->calculatePriceRule($price['price_rule'], $reservation);
            }
        }

        return $amount;
    }

    /**
     * Calculate price for rule by reservation.
     * @param PriceRule $rule
     * @param Reservation $reservation
     * @return int
     */
    private function calculatePriceRule(PriceRule $rule, object $reservation): int
    {
        if ($rule->time_from !== null && $rule->time_to !== null) {
            $startAt = new DateTimeImmutable();
            $startAt = $startAt->setTimestamp($reservation->start_at);
            list($hour, $minute) = explode(':', $rule->time_from, 2);
            $topAt = $startAt->setTime((int) $hour, (int) $minute);
            list($hour, $minute) = explode(':', $rule->time_to, 2);
            $bottomAt = $startAt->setTime((int) $hour, (int) $minute);
            $passTime = $startAt >= $topAt && $startAt < $bottomAt;
        } else {
            $passTime = true;
        }

        switch ($rule->comparison) {
            case '=':
                $passLength = $reservation->length == $rule->from_length;
                break;
            case '>':
                $passLength = $reservation->length > $rule->from_length;
                break;
            case '>=':
                $passLength = $reservation->length >= $rule->from_length;
                break;
            case '<':
                $passLength = $reservation->length < $rule->from_length;
                break;
            case '<=':
                $passLength = $reservation->length <= $rule->from_length;
                break;
            case '!=':
                $passLength = $reservation->length != $rule->from_length;
                break;
            default:
                $passLength = $reservation->length >= $rule->from_length;
        }
        if ($passTime && $passLength) {
            if ($rule->type == PriceRule::TYPE_PER_HOUR) {
                return $rule->price * intval($reservation->length / 60);
            } elseif ($rule->type == PriceRule::TYPE_FIXED) {
                return $rule->price;
            }
        }

        return 0;
    }
}
