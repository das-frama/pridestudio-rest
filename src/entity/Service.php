<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\base\AbstractEntity;

class Service extends AbstractEntity
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var ServiceChild[] */
    public $children;

    /** @var int */
    public $sort;

    /** @var bool */
    public $is_active;

    /** @var int */
    public $created_at;

    /** @var int */
    public $updated_at;

    /** @var string */
    public $created_by;

    /** @var string */
    public $updated_by;
}
