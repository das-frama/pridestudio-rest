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
        $this->userRepo = $userRepo;
    }

    /**
     * Get user by id.
     * @param string $id
     * @param array $include
     * @return User|null
     */
    public function findByID(string $id, array $include = []): ?User
    {
        return $this->userRepo->findOne(['id' => $id], $include);
    }

    /**
     * Get user by email.
     * @param string $email
     * @param array $include
     * @return User|null
     */
    public function findByEmail(string $email, $include = []): ?User
    {
        return $this->userRepo->findOne(['email' => $email], $include);
    }

    /**
     * Get all users.
     * @param array $filter
     * @param array $include
     * @return User[]
     */
    public function findAll(array $filter = [], array $include = []): array
    {
        return $this->userRepo->findAll($filter, $include);
    }

    /**
     * Init super user.
     * @param string $email
     * @param string $password
     * @return User|null
     */
    public function initSuperUser(string $email, string $password): ?User
    {
        $user = new User;
        $user->name = "Super Dude";
        $user->email = $email;
        $user->role = 'super';
        $user->is_active = true;
        $user->updated_at = time();
        $user->setPassword($password);

        $id = $this->userRepo->insert($user);
        if ($id === null) {
            return null;
        }
        $user->id = $id;

        return $user;
    }

    public function create(User $user): ?string
    {
        if ($user->updated_at === null) {
            $user->updated_at = time();
        }
        return $this->userRepo->insert($user);
    }
}
