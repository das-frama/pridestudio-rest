<?php

declare(strict_types=1);

namespace app\entity;

/**
 * Payment class.
 */
class Payment
{
    /** @var int */
    public $id;

    /** @var int */
    public $record_id;

    /** @var int */
    public $client_id;

    /** @var int */
    public $method_id;

    /** @var int */
    public $type_id;

    /** @var float */
    public $amount;

    /** @var int */
    public $status;

    /** @var int */
    public $paid_at;

    /** @var int */
    public $created_at;

    /** @var int */
    public $updated_at;
}
