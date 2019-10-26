<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\base\AbstractEntity;

/**
 * Payment class.
 */
class Payment extends AbstractEntity
{
    const STATUS_NEW = 1;
    const STATUS_DONE = 10;

    const AGGREGATOR_ROBOKASSA = 1;

    /** @var string */
    public $id;

    /** @var string */
    public $method_id;

    /** @var int */
    public $aggregator;

    /** @var int */
    public $total;

    /** @var int */
    public $status;

    /** @var int */
    public $paid_at;

    /** @var int */
    public $created_at;

    /** @var int */
    public $updated_at;
}
