<?php declare(strict_type=1);

namespace app\storage\mysql;

use PDO;
use app\domain\user\User;
use app\domain\user\UserRepository as UserRepositoryInterface;

/**
 * Class UserRepository
 * @package app\storage\mysql
 */
class UserRepository implements UserRepositoryInterface
{
    /** @var PDO */
    protected $storage;

    public function __construct(PDO $conn)
    {
        $this->storage = $conn;
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function findByID(int $id) : ?User
    {
        try {
            $sth = $this->storage->prepare("SELECT * FROM `user` WHERE id=?");
            $sth->execute([$id]);
            $sth->setFetchMode(PDO::FETCH_CLASS, User::class);
            $user = $sth->fetch();

            return $user;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email) : ?User
    {
        try {
            $sth = $this->storage->prepare("SELECT * FROM `user` WHERE email=?");
            $sth->execute([$email]);
            $sth->setFetchMode(PDO::FETCH_CLASS, User::class);
            $user = $sth->fetch();

            return $user;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return User[]
     */
    public function findAll(int $limit, int $offset) : array
    {
        return [];
    }

    /**
     * @return bool
     */
    public function save() : bool
    {
        return false;
    }
}
