<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\Entity;

/**
 * Reservation entity class.
 */
class Reservation extends Entity
{
    public $start_at;
    public $length;
    public $comment;
}
