<?php

declare(strict_types=1);

namespace App\Entity;

use App\Storage\MongoDB\Base\AbstractEntity;

/**
 * Setting class.
 */
class Setting extends AbstractEntity
{
    public string $id;
    public string $key;
    public $value;
    public bool $is_active;
}
