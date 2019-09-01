<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\Entity;

/**
 * Reservation entity class.
 */
class Reservation extends Entity
{
    /** @var int */
    public $start_at;

    /** @var int */
    public $length;

    /** @var int */
    public $comment;
}
