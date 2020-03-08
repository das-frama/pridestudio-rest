<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Base\AbstractEntity;

/**
 * Coupon class.
 */
class Coupon extends AbstractEntity
{
    public string $id;
    public string $code;
    public float $factor;
    public int $length;
    public int $start_at;
    public int $end_at;
    public int $sort;
    public bool $is_active;
    public int $created_at;
    public int $updated_at;
    public string $created_by;
    public string $updated_by;
}
