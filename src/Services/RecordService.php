<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\Client;
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
use DateTimeImmutable;
use Exception;

/**
 * Class RecordService
 * @package App\Services
 */
class RecordService
{
    private RecordRepositoryInterface $repo;
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

//        // Set relations.
//        $this->relations = [
//            'client' => ['client_id', $clientRepo],
//            'hall' => ['hall_id', $hallRepo],
//            'coupon' => ['coupon_id', $couponRepo],
//        ];
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
     * Book a new record.
     * @param Record $record
     * @param Client $client
     * @param Coupon $coupon
     * @return Record|null
     */
    public function book(Record $record, Client $client, Coupon $coupon = null): ?Record
    {
        // Hall.
        $hall = $this->hallRepo->findOne(['id' => $record->hall_id]);
        if (!$hall instanceof Hall) {
            return null;
        }
        // Client.
        $client = $this->upsertClient($client);
        if (isset($client->id)) {
            $record->client_id = $client->id;
        }
        // Total price.
        $record->total = $this->calculatePrice($record, $hall, $coupon);
        $record->status = Record::STATUS_NEW;
        // Payment.
        if (isset($record->payment) && $record->payment instanceof Payment) {
            $record->payment->aggregator = Payment::AGGREGATOR_ROBOKASSA;
            $record->payment->status = Payment::STATUS_NEW;
        }
        // Store record.
        $record = $this->repo->insert($record);
        return $record instanceof Record ? $record : null;
    }

    /**
     * @param Client $client
     * @return Client
     */
    public function upsertClient(Client $client): Client
    {
        $newClient = $this->clientRepo->findOneAndUpdate(['email' => $client->email], $client, true);
        if (!$newClient instanceof Client) {
            $newClient = $this->clientRepo->insert($client);
        }
        return $newClient;
    }

    /**
     * Calculate price for reservations.
     * @param Record $record
     * @param Hall $hall
     * @param Coupon|null $coupon
     * @return int
     * @throws Exception
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
        if ($coupon !== null && isset($coupon->factor)) {
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
     * @throws Exception
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
     * @param Record $record
     * @return Hall|null
     */
    public function findHall(Record $record): ?Hall
    {
        $hall = $this->hallRepo->findOne(['id' => $record->hall_id]);
        return $hall instanceof Hall ? $hall : null;
    }

    /**
     * @param Record $record
     * @return Coupon|null
     */
    public function findCoupon(Record $record): ?Coupon
    {
        $coupon = $this->couponRepo->findOne(['id' => $record->coupon_id]);
        return $coupon instanceof Coupon ? $coupon : null;
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
