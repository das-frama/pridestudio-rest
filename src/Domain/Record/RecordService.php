<?php

declare(strict_types=1);

namespace App\Domain\Record;

use App\Domain\Client\ClientRepositoryInterface;
use App\Domain\Hall\HallRepositoryInterface;
use App\Entity\Client;
use App\Entity\Coupon;
use App\Entity\Hall;
use App\Entity\Payment;
use App\Entity\PriceRule;
use App\Entity\Record;
use App\Entity\Reservation;
use DateTimeImmutable;

class RecordService
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
    )
    {
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
    public function findByID(string $id, array $include = [], array $expand = []): ?Record
    {
        if (!empty($include) && in_array('client', $expand) && !in_array('client_id', $include)) {
            $include[] = 'client_id';
        }
        if (!empty($include) && in_array('hall', $expand) && !in_array('hall_id', $include)) {
            $include[] = 'hall_id';
        }
        $record = $this->recordRepo->findOne(['id' => $id], $include);
        if ($record === null) {
            return null;
        }

        // Expand.
        if (in_array('client', $expand)) {
            $client = $this->clientRepo->findOne(['id' => $record->client_id]);
            $record->setExpand('client', $client);
        }
        if (in_array('hall', $expand)) {
            $hall = $this->hallRepo->findOne(['id' => $record->hall_id]);
            $record->setExpand('hall', $hall);
        }

        return $record instanceof Record ? $record : null;
    }

    /**
     * Get all records.
     * @param array $params
     * @param array $include
     * @param array $expand
     * @return Record[]
     */
    public function findAll(array $params = [], array $include = [], array $expand = []): array
    {
        $page = intval($params['page'] ?? 0);
        $limit = intval($params['limit'] ?? 0);

        // Sort.
        $sort = [];
        if (isset($params['orderBy'])) {
            // Change created_at for id.
            if ($params['orderBy'] === 'created_at') {
                $params['orderBy'] = 'id';
            }
            $sort[$params['orderBy']] = $params['ascending'] == 0 ? -1 : 1;
        } else {
            $sort['id'] = -1;
        }

        // Skip.
        $skip = 0;
        if ($page > 0) {
            $skip = $limit * ($page - 1);
        }

        // Query.
        $filter = [];
        if (isset($params['query'])) {
            $filter = ['id' => $params['query']];
            return $this->recordRepo->search($filter, $limit, $skip, $sort, $include);
        }
        if (in_array('client', $expand) && !in_array('client_id', $include)) {
            $include[] = 'client_id';
        }
        $items = $this->recordRepo->findAll($filter, $limit, $skip, $sort, $include);

        // References.
        if (in_array('client', $expand)) {
            $clientIDs = array_column($items, 'client_id');
            $clients = $this->clientRepo->findAll(['id' => $clientIDs], 0, 0, [], ['id', 'name']);
            $clients = array_column($clients, null, 'id');
            foreach ($items as $i => $item) {
                $clientID = $clientIDs[$i];
                $item->setExpand('client', $clients[$clientID]);
            }
        }

        return $items;
    }

    /**
     * Check if given record is exists.
     * @param string $id
     * @return bool
     */
    public function isExists(string $id): bool
    {
        return $this->recordRepo->isExists(['id' => $id]);
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
            Record::STATUS_NOTPAID => 'Не оплачен',
            Record::STATUS_PAID => 'Оплачен',
            Record::STATUS_CASH => 'Наличными',
            Record::STATUS_DONE => 'Выполнен',
        ];
    }

    /**
     * Count halls.
     * @return int
     */
    public function count()
    {
        return $this->recordRepo->count();
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
     * Check coupon and return it's ID.
     * @param string $code
     * @return Coupon|null
     */
    public function findCouponByCode(string $code, array $include = []): ?Coupon
    {
        $coupon = $this->couponRepo->findOne(['code' => $code], $include);
        return $coupon instanceof Coupon ? $coupon : null;
    }

    /**
     * Calculate price for rule by reservation.
     * @param PriceRule $rule
     * @param Reservation $reservation
     * @param int $basePrice
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
     * Create a new record.
     * @param Record $record
     * @param Client $c
     * @param string $couponCode
     * @return Record|null
     */
    public function create(Record $record, Client $c, string $couponCode = null): ?Record
    {
        // Client. If not exist then create one.
        $filter = ['email' => $c->email, 'phone' => $c->phone];
        $client = $this->clientRepo->findOneAndUpdate($filter, $c, ['id'], true);
        if ($client === null) {
            $client = $this->clientRepo->insert($c);
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
     * Update existing record.
     * @param Record $record
     * @param Client $c
     * @return Record|null
     */
    public function update(Record $record, Client $c): ?Record
    {
        // Client. If not exist then create one.
        $filter = ['email' => $c->email, 'phone' => $c->phone];
        $client = $this->clientRepo->findOneAndUpdate($filter, $c, ['id'], true);
        if ($client === null) {
            $client = $this->clientRepo->insert($c);
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
     * Delete an existing record.
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        return $this->recordRepo->delete($id);
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
