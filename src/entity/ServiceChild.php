<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\Entity;

class ServiceChild extends Entity
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var bool */
    public $is_active;
}
