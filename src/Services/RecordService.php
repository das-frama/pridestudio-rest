<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\Coupon;
use App\Entities\Hall;
use App\Entities\Payment;
use App\Entities\PriceRule;
use App\Entities\Record;
use App\Entities\Reservation;
use App\Repositories\ClientRepositoryInterface;
use App\Repositories\CouponRepositoryInterface;
use App\Repositories\HallRepositoryInterface;
use App\Repositories\RecordRepositoryInterface;
use App\Services\Base\AbstractService;
use DateTimeImmutable;

/**
 * Class RecordService
 * @package App\Services
 */
class RecordService extends AbstractService
{
    private RecordRepositoryInterface $recordRepo;
    private ClientRepositoryInterface $clientRepo;
    private CouponRepositoryInterface $couponRepo;
    private HallRepositoryInterface $hallRepo;


    /**
     * RecordService constructor.
     * @param RecordRepositoryInterface $recordRepo
     * @param ClientRepositoryInterface $clientRepo
     * @param CouponRepositoryInterface $couponRepo
     * @param HallRepositoryInterface $hallRepo
     */
    public function __construct(
        RecordRepositoryInterface $recordRepo,
        ClientRepositoryInterface $clientRepo,
        CouponRepositoryInterface $couponRepo,
        HallRepositoryInterface $hallRepo
    ) {
        $this->repo = $recordRepo;
        $this->clientRepo = $clientRepo;
        $this->couponRepo = $couponRepo;
        $this->hallRepo = $hallRepo;

        // Set relations.
        $this->relations = [
            'client' => ['client_id', $clientRepo],
            'hall' => ['hall_id', $hallRepo],
            'coupon' => ['coupon_id', $couponRepo],
        ];
    }

    /**
     * Get status list.
     * @return array
     */
    public function statuses(): array
    {
        return [
            Record::STATUS_CANCELED => 'Отменён',
            Record::STATUS_NEW => 'Новый',
            Record::STATUS_PREPAID => 'Предоплата',
            Record::STATUS_NOT_PAID => 'Не оплачен',
            Record::STATUS_PAID => 'Оплачен',
            Record::STATUS_CASH => 'Наличными',
            Record::STATUS_DONE => 'Выполнен',
        ];
    }

    /**
     * Check coupon.
     * @param string $code
     * @return Coupon|null
     */
    public function findCouponByCode(string $code): ?Coupon
    {
        $coupon = $this->couponRepo->findOne(['code' => $code]);
        return $coupon instanceof Coupon ? $coupon : null;
    }

    /**
     * Create a new record.
     * @param Record $record
     * @param string $couponCode
     * @return Record|null
     */
    public function create(Record $record, string $couponCode = null): ?Record
    {
        // Client. If not exist then create one.
        $filter = [
            'email' => $record->client->email,
            'phone' => $record->client->phone,
        ];
        $client = $this->clientRepo->findOneAndUpdate($filter, $record->client, true);
        if ($client === null) {
            $client = $this->clientRepo->insert($record->client);
        }
        if ($client->id !== null) {
            $record->client_id = $client->id;
        }

        // Hall.
        $hall = $this->hallRepo->findOne(['id' => $record->hall_id], ['id', 'base_price', 'prices']);
        if (!($hall instanceof Hall)) {
            return null;
        }

        // Coupon.
        $coupon = null;
        if ($couponCode !== null) {
            $coupon = $this->couponRepo->findOne(['code' => $couponCode], ['id', 'factor']);
            if ($coupon instanceof Coupon) {
                $record->coupon_id = $coupon->id;
            }
        }

        // Total price.
        $record->total = $this->calculatePrice($record, $hall, $coupon);
        $record->status = Record::STATUS_NEW;

        // Payment.
        if ($record->payment instanceof Payment) {
            $record->payment->aggregator = Payment::AGGREGATOR_ROBOKASSA;
            $record->payment->status = Payment::STATUS_NEW;
        }

        // Save record.
        $record = $this->recordRepo->insert($record);
        return $record instanceof Record ? $record : null;
    }

    /**
     * Calculate price for reservations.
     * @param Record $record
     * @param Hall $hall
     * @param Coupon|null $coupon
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
     * Calculate price for rule by reservation.
     * @param PriceRule $rule
     * @param Reservation $reservation
     * @param int $basePrice
     * @return int
     * @throws \Exception
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

        // Day of the week.
        if ($rule->schedule_mask !== null) {
            $day = PriceRule::getWeekday($reservation->start_at);
            if (($rule->schedule_mask & $day) !== $day) {
                return $basePrice * $hours;
            }
        }

        // Fixed price.
        if ($rule->type == PriceRule::TYPE_FIXED) {
            return $rule->price;
        }
        // Price per hour.
        $hoursToCount = $hours;
        if ($rule->time_from !== null && $rule->time_to !== null) {
            $date = (new DateTimeImmutable())->setTimestamp($reservation->start_at);
            list($hour, $minute) = explode(':', $rule->time_from, 2);
            $topAt = $date->setTime((int)$hour, (int)$minute)->getTimestamp();
            list($hour, $minute) = explode(':', $rule->time_to, 2);
            $bottomAt = $date->setTime((int)$hour, (int)$minute)->getTimestamp();
            if ($hour === "00") {
                $bottomAt += 24 * 60 * 60;
            }
            $diff = min($reservation->start_at + $reservation->length * 60, $bottomAt) -
                max($reservation->start_at, $topAt);
            $hoursToCount = $diff > 0 ? $diff / 60 / 60 : 0;
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
     * Update existing record.
     * @param Record $record
     * @return Record|null
     */
    public function update(Record $record): ?Record
    {
        // Client. If not exist then create one.
        $filter = [
            'email' => $record->client->email,
            'phone' => $record->client->phone,
        ];
        $client = $this->clientRepo->findOneAndUpdate($filter, $record->client, true);
        if ($client === null) {
            $client = $this->clientRepo->insert($record->client);
        }
        if ($client->id !== null) {
            $record->client_id = $client->id;
        }

        // Hall.
        $hall = $this->hallRepo->findOne(['id' => $record->hall_id], ['id', 'base_price', 'prices']);
        if (!($hall instanceof Hall)) {
            return null;
        }

        // // Coupon.
        // $coupon = null;
        // if ($couponCode !== null) {
        //     $coupon = $this->couponRepo->findOne(['code' => $couponCode], ['id', 'factor']);
        //     if ($coupon !== null) {
        //         $record->coupon_id = $coupon->id;
        //     }
        // }

        // Total price.
        $record->total = $record->total ?: $this->calculatePrice($record, $hall);

        // Payment.
        // if ($record->payment instanceof Payment) {
        //     $record->payment->aggregator = Payment::AGGREGATOR_ROBOKASSA;
        //     $record->payment->status = Payment::STATUS_NEW;
        //     $record->payment->updated_at = time();
        // }

        // Save record.
        $record = $this->recordRepo->update($record);
        return $record instanceof Record ? $record : null;
    }

    /**
     * Get payment url from record.
     * @param Record $record
     * @return string
     */
    public function getPaymentURL(Record $record): string
    {
        if ($record->payment instanceof Payment) {
            return 'Https://test.robokassa.ru/ru';
        }
        return '';
    }
}
