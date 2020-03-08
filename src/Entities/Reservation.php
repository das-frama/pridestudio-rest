<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Base\AbstractEntity;

/**
 * Reservation AbstractEntity class.
 */
class Reservation extends AbstractEntity
{
    public int $start_at;
    public int $length;
    public string $comment = '';
}
