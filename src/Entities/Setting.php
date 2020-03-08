<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Base\AbstractEntity;

/**
 * Setting class.
 */
class Setting extends AbstractEntity
{
    public string $id;
    public string $key;
    /**
     * @var string|int
     */
    public $value;
    public bool $is_active;
}
