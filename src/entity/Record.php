<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\Entity;
use MongoDB\BSON\UTCDateTime;

/**
 * Record entity class.
 */
class Record extends Entity
{
    /** @var string */
    public $id;

    /** @var string */
    public $client_id;

    /** @var string */
    public $hall_id;

    /** @var Reservation[] */
    public $reservations;

    /** @var string */
    public $payment_id;

    /** @var string */
    public $promo_id;

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
    public function bsonUnserialize(array $data): void
    {
        parent::bsonUnserialize($data);
        foreach ($this->reservations as &$reservation) {
            $entity = new Reservation;
            if ($reservation['start_at'] instanceof UTCDateTime) {
                $entity->start_at = $reservation['start_at']->toDateTime()->getTimestamp();
            }
            $entity->length = $reservation['length'];
            $entity->comment = $reservation['comment'];
            $reservation = $entity;
        }
    }
}
