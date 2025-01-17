<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Base\AbstractEntity;

/**
 * Class ServiceChild
 * @package App\Entity
 */
class ServiceChild extends AbstractEntity
{
    public string $id;
    public string $name;
    public string $comment = '';
    public bool $has_children = false;
    public bool $is_active = false;
}
