<?php

declare(strict_types=1);

namespace app\domain\record;

use app\domain\client\ClientRepositoryInterface;
use app\domain\hall\HallRepositoryInterface;
use app\entity\Client;
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

    /** @var ClientRepositoryInterface */
    private $clientRepo;

    /** @var CouponRepositoryInterface */
    private $couponRepo;

    /** @var HallRepositoryInterface */
    private $hallRepo;

    public function __construct(
        RecordRepositoryInterface $recordRepo,
        ClientRepositoryInterface $clientRepo,
        CouponRepositoryInterface $couponRepo,
        HallRepositoryInterface $hallRepo
    ) {
        $this->recordRepo = $recordRepo;
        $this->clientRepo = $clientRepo;
        $this->couponRepo = $couponRepo;
        $this->hallRepo = $hallRepo;
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
        return $this->recordRepo->findAll([], 0, 0, [], $include);
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
        // Calculate reservations.
        foreach ($record->reservations as $reservation) {
            if (empty($hall->prices)) {
                $amount += $hall->base_price * intval($reservation->length / 60);
                continue;
            }
            foreach ($hall->prices as $price) {
                // If price has services.
                if (!empty($price->service_ids)) {
                    // And they should intersect with record services.
                    if (empty(array_intersect($record->service_ids, $price->service_ids))) {
                        continue;
                    }
                }
                $amount += $this->calculatePriceRule($price, $reservation, $hall->base_price);
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
     * @param int $base_price
     * @return int
     */
    private function calculatePriceRule(PriceRule $rule, Reservation $reservation, int $basePrice): int
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

        $hours = $reservation->length / 60;
        if (!$passLength) {
            return $basePrice * $hours;
        }
        // Fixed price.
        if ($rule->type == PriceRule::TYPE_FIXED) {
            return $rule->price;
        }
        // Price per hour.
        $hoursToCount = 0;
        if ($rule->time_from !== null && $rule->time_to !== null) {
            $date = (new DateTimeImmutable())->setTimestamp($reservation->start_at);
            list($hour, $minute) = explode(':', $rule->time_from, 2);
            $topAt = $date->setTime((int) $hour, (int) $minute)->getTimestamp();
            list($hour, $minute) = explode(':', $rule->time_to, 2);
            $bottomAt = $date->setTime((int) $hour, (int) $minute)->getTimestamp();
            if ($hour === "00") {
                $bottomAt += 24 * 60 * 60;
            }
            $diff = min($reservation->start_at + $reservation->length * 60, $bottomAt) -
                max($reservation->start_at, $topAt);
            $hoursToCount = $diff > 0 ? $diff / 60 / 60 : 0;
        } else {
            $hoursToCount = $hours;
        }

        $amount = 0;
        if ($hoursToCount > 0) {
            $amount += $rule->price * $hoursToCount;
        }
        $uncountHours = $hours - $hoursToCount;
        if ($uncountHours > 0) {
            $amount += $basePrice * $uncountHours;
        }

        return $amount;
    }

    /**
     * Create a new record.
     * @param Record $record
     * @param Client $client
     * @param string $couponCode
     * @return string|null
     */
    public function create(Record $record, Client $c, string $couponCode = null): ?string
    {
        // Find client. If not exist then create one.
        $filter = ['email' => $c->email, 'phone' => $c->phone];
        $client = $this->clientRepo->findOneAndUpdate($filter, $c, ['id'], true);
        if ($client === null) {
            $client = clone $c;
            $client->id = $this->clientRepo->insert($client);
        }
        if ($client->id !== null) {
            $record->client_id = $client->id;
        }
        // Hall.
        $hall = $this->hallRepo->findOne(['id' => $record->hall_id], ['id', 'base_price', 'prices']);
        if ($hall === null) {
            return null;
        }
        // Coupon.
        $coupon = null;
        if ($couponCode !== null) {
            $coupon = $this->couponRepo->findOne(['code' => $couponCode], ['id', 'factor']);
            if ($coupon !== null) {
                $record->coupon_id = $coupon->id;
            }
        }
        // $hall = $this-> $record->hall_id;

        $record->total = $this->calculatePrice($record, $hall, $coupon);
        $record->updated_at = time();
        return $this->recordRepo->insert($record);
    }
}
