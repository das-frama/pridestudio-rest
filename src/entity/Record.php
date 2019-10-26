<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\base\AbstractEntity;
use MongoDB\BSON\ObjectId;

/**
 * Record AbstractEntity class.
 */
class Record extends AbstractEntity
{
    /** @var string */
    public $id;

    /** @var string */
    public $client_id;

    /** @var string */
    public $hall_id;

    /** @var Reservation[] */
    public $reservations = [];

    /** @var array */
    public $service_ids = [];

    /** @var string */
    public $payment_id;

    /** @var string */
    public $coupon_id;

    /** @var int */
    public $total;

    /** @var string */
    public $comment;

    /** @var int */
    public $status;

    /** @var int */
    public $created_at;

    /** @var int */
    public $updated_at;

    /** @var string */
    public $created_by;

    /** @var string */
    public $updated_by;

    /**
     * {@inheritDoc}
     */
    public function load(array $data, array $safe = []): void
    {
        parent::load($data, $safe);
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
        if ($this->payment_id) {
            $bson['payment_id'] = new ObjectId((string) $this->payment_id);
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
