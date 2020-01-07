<?php

declare(strict_types=1);

namespace App\Entity;

use App\Storage\MongoDB\Base\AbstractEntity;
use MongoDB\BSON\ObjectId;

/**
 * Record AbstractEntity class.
 */
class Record extends AbstractEntity
{
    // Statuses.
    const STATUS_CANCELED = 0;
    const STATUS_NEW = 1;
    const STATUS_PREPAID = 2;
    const STATUS_NOTPAID = 3;
    const STATUS_PAID = 4;
    const STATUS_CASH = 5;
    const STATUS_DONE = 10;

    public string $id;
    public string $client_id;
    public string $hall_id;
     /** @var Reservation[] */
    public array $reservations = [];
    public array $service_ids = [];
    public ?Payment $payment;
    public string $coupon_id;
    public int $total;
    public string $comment;
    public int $status;
    public int $created_at;
    public int $updated_at;
    public string $created_by;
    public string $updated_by;

    /**
     * {@inheritDoc}
     */
    public function load(array $data, array $safe = []): void
    {
        parent::load($data, $safe);
        if (isset($data['payment']) && in_array('payment', $safe)) {
            $this->payment = new Payment;
            $this->payment->load($data['payment'], ['method_id']);
        }
        if (isset($data['reservations']) && is_array($data['reservations']) && in_array('reservations', $safe)) {
            $this->reservations = [];
            foreach ($data['reservations'] as $d) {
                $reservation = new Reservation;
                $reservation->load($d);
                $this->reservations[] = $reservation;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function bsonSerialize(): array
    {
        $bson = parent::bsonSerialize();
        if ($this->client_id) {
            $bson['client_id'] = new ObjectId($this->client_id);
        }
        if ($this->hall_id) {
            $bson['hall_id'] = new ObjectId($this->hall_id);
        }
        if ($this->coupon_id) {
            $bson['coupon_id'] = new ObjectId($this->coupon_id);
        }
        $bson['service_ids'] = array_map(function (string $id) {
            return new ObjectId($id);
        }, $this->service_ids);

        return $bson;
    }
}
