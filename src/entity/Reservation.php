<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\base\AbstractEntity;

/**
 * Reservation AbstractEntity class.
 */
class Reservation extends AbstractEntity
{
    /** @var int */
    public $start_at;

    /** @var int */
    public $length;

    /** @var int */
    public $comment;
}
