<?php
declare(strict_types=1);

namespace App\Entities;

use App\Entities\Base\AbstractEntity;

/**
 * Client class represents a client Entity.
 */
class Client extends AbstractEntity
{
    public string $id;
    public string $name;
    public string $email;
    public string $phone;
    public int $sex;
    public string $comment;
    public int $created_at;
    public int $updated_at;
}
