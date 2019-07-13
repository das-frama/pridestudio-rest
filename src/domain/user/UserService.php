<?php

declare(strict_types=1);

namespace app\domain\user;

class UserService
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function findByID(int $id): ?User
    {
        return $this->userRepo->findByID($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->userRepo->findByEmail($email);
    }

    /**
     * @return User[]
     */
    public function findAll(int $limit, int $offset): array
    {
        return $this->userRepo->findAll($limit, $offset);
    }
}
