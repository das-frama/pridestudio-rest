<?php

declare(strict_types=1);

namespace App\Entity;

use App\Storage\MongoDB\Base\AbstractEntity;

/**
 * Service class represents an additional service from booking form.
 */
class Service extends AbstractEntity
{
    public string $id;
    public string $name;
    /** @var ServiceChild[] */
    public array $children;
    public int $sort;
    public bool $is_active;
    public int $created_at;
    public int $updated_at;
    public string $created_by;
    public string $updated_by;
}
