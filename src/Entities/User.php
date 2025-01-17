<?php
declare(strict_types=1);

namespace App\Entities;

use App\Entities\Base\AbstractEntity;

class User extends AbstractEntity
{
    public string $id;
    public string $email;
    public string $name;
    public string $phone;
    public string $password_hash;
    public string $role;
    public bool $is_active;
    public string $refresh_token;
    public int $created_at;
    public int $updated_at;
    public string $created_by;
    public string $updated_by;

    protected array $fillable = ['name', 'email', 'is_active', 'role'];
    protected array $public = [
        'id',
        'email',
        'name',
        'phone',
        'role',
        'is_active',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];

    /**
     * {@inheritDoc}
     */
    public function load(array $data, array $safe = []): void
    {
        parent::load($data, $safe);
        if (isset($data['password']) && in_array('password', $safe)) {
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
