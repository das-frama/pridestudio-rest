<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\base\AbstractEntity;

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
}
