<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\base\AbstractEntity;

/**
 * Client class.
 */
class Client extends AbstractEntity
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $email;

    /** @var string */
    public $phone;

    /** @var int */
    public $sex;

    /** @var string */
    public $comment;

    /** @var int */
    public $created_at;

    /** @var int */
    public $updated_at;
}
