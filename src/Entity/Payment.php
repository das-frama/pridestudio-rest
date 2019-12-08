<?php

declare(strict_types=1);

namespace App\Entity;

use App\Storage\MongoDB\Base\AbstractEntity;

/**
 * Payment class.
 */
class Payment extends AbstractEntity
{
    const STATUS_NEW = 1;
    const STATUS_DONE = 10;

    const AGGREGATOR_ROBOKASSA = 1;

    public string $id;
    public string $method_id;
    public int $aggregator;
    public int $total;
    public int $status;
    public int $paid_at;
    public int $created_at;
    public int $updated_at;
}
