<?php

declare(strict_types=1);

namespace app\domain\user;

use app\entity\User;

interface UserRepositoryInterface
{
    public function findByID(int $id): ?User;
    public function findByEmail(string $email): ?User;
    /**
     * @return User[]
     */
    public function findAll(int $limit, int $offset): array;
    public function save(): bool;
}
