<?php

declare(strict_types=1);

namespace app\domain\user;

use app\entity\User;

class UserService
{
    /** @var UserRepositoryInterface */
    private $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;;
    }

    /**
     * Get user by id.
     * @param string $id
     * @return User|null
     */
    public function findByID(string $id): ?User
    {
        return $this->userRepo->findByID($id);
    }

    /**
     * Get user by email.
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->userRepo->findByEmail($email);
    }

    /**
     * Get all users.
     * @param int $limit
     * @param int $offset
     * @return User[]
     */
    public function findAll(int $limit, int $offset): array
    {
        return $this->userRepo->findAll($limit, $offset);
    }
}
