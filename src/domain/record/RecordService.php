<?php

declare(strict_types=1);

namespace app\domain\record;

use app\entity\Coupon;
use app\entity\Hall;
use app\entity\PriceRule;
use app\entity\Record;
use app\entity\Reservation;
use DateTimeImmutable;

class RecordService
{
    /** @var RecordRepositoryInterface */
    private $recordRepo;

    /** @var CouponRepositoryInterface */
    private $couponRepo;

    public function __construct(RecordRepositoryInterface $recordRepo, CouponRepositoryInterface $couponRepo)
    {
        $this->recordRepo = $recordRepo;
        $this->couponRepo = $couponRepo;
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
    public function calculatePrice(Record $record, Hall $hall, Coupon $coupon = null): int
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
        // Apply coupon discount.
        if ($coupon !== null && $coupon->factor !== null) {
            $amount -= intval($amount * $coupon->factor);
        }

        return $amount;
    }

    /**
     * Check coupon and return it's ID.
     * @param string $code
     * @return Coupon|null
     */
    public function findCouponByCode(string $code, array $include = []): ?Coupon
    {
        return $this->couponRepo->findOne(['code' => $code], $include);
    }

    /**
     * Calculate price for rule by reservation.
     * @param PriceRule $rule
     * @param Reservation $reservation
     * @return int
     */
    private function calculatePriceRule(PriceRule $rule, object $reservation): int
    {
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
        // Fixed price.
        if ($rule->type == PriceRule::TYPE_FIXED && $passLength) {
            return $rule->price;
        }
        // Price per hour.
        $hoursToCount = $reservation->length / 60;
        if ($rule->time_from !== null && $rule->time_to !== null) {
            $date = (new DateTimeImmutable())->setTimestamp($reservation->start_at);
            list($hour, $minute) = explode(':', $rule->time_from, 2);
            $topAt = $date->setTime((int) $hour, (int) $minute)->getTimestamp();
            list($hour, $minute) = explode(':', $rule->time_to, 2);
            $bottomAt = $date->setTime((int) $hour, (int) $minute)->getTimestamp();
            if ($hour === "00") {
                $bottomAt += 24 * 60 * 60;
            }
            $diff = min($reservation->start_at + $reservation->length * 60, $bottomAt) - max($reservation->start_at, $topAt);
            $hoursToCount = $diff > 0 ? $diff / 60 / 60 : 0;
        }
        if ($hoursToCount > 0 && $passLength) {
            return $rule->price * $hoursToCount;
        }

        return 0;
    }
}
