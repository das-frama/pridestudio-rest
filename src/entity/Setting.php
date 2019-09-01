<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\Entity;

/**
 * Setting class.
 */
class Setting extends Entity
{
    /** @var int */
    public $id;

    /** @var string */
    public $key;

    /** @var int|string */
    public $value;

    /** @var bool */
    public $is_active;
}
