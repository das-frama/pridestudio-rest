<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\Client;
use App\Entities\Coupon;
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
     * Store a new record.
     * @param Record $record
     * @return Record|null
     */
    public function book(Record $record): ?Record
    {
        // Hall.
        $record = $this->withHall($record);
        if ($record->hall === null) {
            return null;
        }
        // Client.
        $record = $this->withClient($record, true);
        // Coupon.
        $record = $this->withCoupon($record);
        // Total price.
        $record->total = $this->calculatePrice($record);
        $record->status = Record::STATUS_NEW;
        // Payment.
        if (isset($record->payment) && $record->payment instanceof Payment) {
            $record->payment->aggregator = Payment::AGGREGATOR_ROBOKASSA;
            $record->payment->status = Payment::STATUS_NEW;
        }
        // Remove relations.
        unset($record->client);
        unset($record->hall);
        unset($record->coupon);
        // Store record.
        $record = $this->repo->insert($record);
        return $record instanceof Record ? $record : null;
    }

    /**
     * @param Record $record
     * @return Record
     */
    public function withHall(Record $record): Record
    {
        $newRecord = clone $record;
        $newRecord->hall = $this->hallRepo->findOne(['id' => $record->hall_id]);
        return $newRecord;
    }

    /**
     * @param Record $record
     * @param bool $upsert
     * @return Record
     */
    public function withClient(Record $record, $upsert = false): Record
    {
        $newRecord = clone $record;
        if (isset($newRecord->client_id)) {
            $newRecord->client = $this->clientRepo->findOne(['id' => $record->client_id]);
        } elseif (isset($newRecord->client)) {
            if ($upsert) {
                $newRecord->client = $this->upsertClient($newRecord->client);
            } else {
                $newRecord->client = $this->clientRepo->findOne(['email' => $newRecord->client->email]);
            }
            if (isset($newRecord->client->id)) {
                $newRecord->client_id = $newRecord->client->id;
            }
        }
        return $newRecord;
    }

    /**
     * Upsert client (insert or update).
     * @param Client $client
     * @return Client|null
     */
    public function upsertClient(Client $client): ?Client
    {
        unset($client->id);
        $newClient = $this->clientRepo->findOneAndUpdate(['email' => $client->email], $client, true);
        if ($newClient === null) {
            $newClient = $this->clientRepo->insert($client);
        }
        return $newClient instanceof Client ? $newClient : null;
    }

    /**
     * @param Record $record
     * @return Record
     */
    public function withCoupon(Record $record): Record
    {
        $newRecord = clone $record;
        if (isset($newRecord->coupon_id)) {
            $newRecord->coupon = $this->couponRepo->findOne(['id' => $record->coupon_id]);
        } elseif (isset($newRecord->coupon->code)) {
            $newRecord->coupon = $this->couponRepo->findOne(['code' => $record->coupon->code]);
            if (isset($newRecord->coupon->id)) {
                $newRecord->coupon_id = $record->coupon->id;
            }
        }
        return $newRecord;
    }

    /**
     * Calculate price for reservations.
     * @param Record $record
     * @return int
     * @throws Exception
     */
    public function calculatePrice(Record $record): int
    {
        if (empty($record->reservations) || !isset($record->hall)) {
            return 0;
        }

        $amount = 0;
        // Calculate reservations.
        foreach ($record->reservations as $reservation) {
            if (empty($record->hall->prices)) {
                $amount += $record->hall->base_price * intval($reservation->length / 60);
                continue;
            }
            foreach ($record->hall->prices as $price) {
                // If price has services.
                if (!empty($price->service_ids)) {
                    // And they should intersect with record services.
                    if (empty(array_intersect($record->service_ids, $price->service_ids))) {
                        continue;
                    }
                }
                $amount += $this->calculatePriceRule($price, $reservation, $record->hall->base_price);
            }
        }
        // Apply coupon discount.
        if (isset($record->coupon) && isset($record->coupon->factor)) {
            $amount -= intval($amount * $record->coupon->factor);
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
