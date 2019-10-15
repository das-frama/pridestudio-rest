<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\base\AbstractEntity;

class ServiceChild extends AbstractEntity
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var bool */
    public $is_active;
}
