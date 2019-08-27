<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\Entity;

/**
 * Record entity class.
 */
class Record extends Entity
{
    /** @var int */
    public $id;

    /** @var int */
    public $client_id;

    /** @var int */
    public $hall_id;

    /** @var Reservation[] */
    public $reservations;

    /** @var int */
    public $payment_id;

    /** @var int */
    public $promo_id;

    /** @var float */
    public $total;

    /** @var string */
    public $comment;

    /** @var int */
    public $status;

    /** @var int */
    public $created_at;

    /** @var int */
    public $updated_at;

    /** @var int */
    public $created_by;

    /** @var int */
    public $updated_by;

    /**
     * {@inheritDoc}
     */
    public function bsonUnserialize(array $data): void
    {
        parent::bsonUnserialize($data);
        foreach ($this->reservations as &$reservation) {
            $entity = new Reservation;
            $entity->start_at = $reservation['start_at']->toDateTime()->getTimestamp();
            $entity->length = $reservation['length'];
            $entity->comment = $reservation['comment'];
            $reservation = $entity;
        }
    }
}
