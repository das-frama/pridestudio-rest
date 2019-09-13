<?php

declare(strict_types=1);

namespace app\domain\record;

use app\entity\Hall;
use app\entity\PriceRule;
use app\entity\Record;
use app\entity\Reservation;
use DateTimeImmutable;

class RecordService
{
    /** @var RecordRepositoryInterface */
    private $recordRepo;

    public function __construct(RecordRepositoryInterface $recordRepo)
    {
        $this->recordRepo = $recordRepo;
    }

    /**
     * Get record by id.
     * @param string $id
     * @param array $include
     * @return Record|null
     */
    public function findByID(string $id, array $include = []): ?Record
    {
        return $this->recordRepo->findOne(['id' => $id], $include);
    }

    /**
     * Get all records.
     * @param array $include
     * @return Record[]
     */
    public function findAll(array $include = []): array
    {
        return $this->recordRepo->findAll([], $include);
    }

    /**
     * Calculate price for reservations.
     * @param Records $record
     * @param Hall $hall
     * @return int
     */
    public function calculatePrice(Record $record, Hall $hall): int
    {
        if (empty($record->reservations)) {
            return 0;
        }
        $amount = 0;
        $totalLength = 0;
        $calculateBasePrice = empty($hall->prices);

        // Calculate reservations.
        foreach ($record->reservations as $reservation) {
            $totalLength += $reservation->length;
            if ($calculateBasePrice) {
                $amount += $hall->base_price * intval($reservation->length / 60);
                continue;
            }

            foreach ($hall->prices as $price) {
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
