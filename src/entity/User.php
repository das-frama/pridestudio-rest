<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\Entity;

class User extends Entity
{
    /** @var string */
    public $id;

    /** @var string */
    public $email;

    /** @var string */
    public $name;

    /** @var string */
    public $phone;

    /** @var string */
    public $password_hash;

    /** @var string */
    public $role;

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

    // public function __construct()
    // {
    // settype($this->id, 'int');
    // settype($this->role, 'int');
    // settype($this->status, 'boolean');
    // settype($this->created_at, 'int');
    // settype($this->updated_at, 'int');
    // settype($this->created_by, 'string');
    // settype($this->updated_by, 'string');
    // }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        if ($hash === false) {
            return;
        }
        $this->password_hash = $hash;
    }

    /**
     * Verify password.
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }
}
