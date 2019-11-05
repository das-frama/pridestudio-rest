<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\base\AbstractEntity;

class User extends AbstractEntity
{
    protected $public = ['id', 'email', 'name', 'phone', 'role', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'];

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

    /**
     * {@inheritDoc}
     */
    public function load(array $data, array $safe = []): void
    {
        parent::load($data, $safe);
        if (isset($data['password']) && in_array('passoword', $safe)) {
            $this->setPassword($data['password']);
        }
    }

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
