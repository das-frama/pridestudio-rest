<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\Entity;

/**
 * Coupon class.
 */
class Coupon extends Entity
{
    /** @var int */
    public $id;

    /** @var string */
    public $code;

    /** @var float */
    public $factor;

    /** @var int */
    public $length;

    /** @var int */
    public $start_at;

    /** @var int */
    public $end_at;

    /** @var int */
    public $sort;

    /** @var bool */
    public $is_active;

    /** @var int */
    public $created_at;

    /** @var int */
    public $updated_at;

    /** @var string */
    public $created_by;

    /** @var string */
    public $updated_by;
}
