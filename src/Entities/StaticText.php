<?php
declare(strict_types=1);

namespace App\Entities;

/**
 * Class StaticText
 * @package App\Entities
 */
class StaticText extends Base\AbstractEntity
{
    public string $id;
    public string $key;
    public string $text;
    public int $created_at;
}
