<?php

declare(strict_types=1);

namespace App\Entity;

use App\Storage\MongoDB\Base\AbstractEntity;

/**
 * Reservation AbstractEntity class.
 */
class Reservation extends AbstractEntity
{
    public int $start_at;
    public int $length;
    public string $comment;
}
