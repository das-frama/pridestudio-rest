<?php declare(strict_type=1);
namespace app\domain\user;

class UserService
{
    /**
     * @var UserRepository
     */
    private $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function findByID(int $id) : ?User
    {
        return $this->userRepo->findByID($id);
    }

    public function findByEmail(string $email) : ?User
    {
        return $this->userRepo->findByEmail($email);
    }

    /**
     * @return User[]
     */
    public function findByAll(int $limit, int $offset) : array
    {
        return $this->userRepo->findByAll($limit, $offset);
    }
}
