<?php

declare(strict_types=1);

namespace app\domain\user;

use app\entity\User;

interface UserRepositoryInterface
{
    /**
     * Find one hall by filter.
     * @param array $filter
     * @param array $include
     * @return User|null
     */
    public function findOne(array $filter, array $include = []): ?User;

    /**
     * Find all halls.
     * @param array $filter
     * @param array $include
     * @return User[]
     */
    public function findAll(array $filter, array $include = []): array;

    /**
     * Insert a new user into storage.
     * @param User $user
     * @return string|null user's id.
     */
    public function insert(User $user): ?string;

    /**
     * Check if user exists by filter.
     * @param array $filter
     * @return bool
     */
    public function isExists(array $filter): bool;

    /**
     * Init user collection.
     * @return bool
     */
    public function init(): bool;
}
