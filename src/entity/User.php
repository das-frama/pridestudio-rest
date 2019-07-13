<?php

declare(strict_types=1);

namespace app\entity;

class User
{
    public $id;
    public $email;
    public $auth_key;
    public $access_token;
    public $password_hash;
    public $password_reset_token;
    public $role;
    public $status;
    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;

    public function __construct()
    {
        settype($this->id, 'int');
        settype($this->role, 'int');
        settype($this->status, 'int');
        settype($this->created_at, 'int');
        settype($this->updated_at, 'int');
        settype($this->created_by, 'int');
        settype($this->updated_by, 'int');
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
