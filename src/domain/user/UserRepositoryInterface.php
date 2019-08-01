<?php

declare(strict_types=1);

namespace app\domain\user;

use app\entity\User;

interface UserRepositoryInterface
{
    public function findByID(string $id): ?User;
    public function findByEmail(string $email): ?User;

    /**
     * @param int $limit
     * @param int $offset
     * @return User[]
     */
    public function findAll(int $limit, int $offset): array;
    public function save(): bool;
}
