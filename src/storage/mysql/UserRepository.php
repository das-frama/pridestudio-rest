<?php

declare(strict_types=1);

namespace app\storage\mysql;

use app\entity\User;
use app\domain\user\UserRepositoryInterface;
use PDO;

/**
 * Class UserRepository
 * @package app\storage\mysql
 */
class UserRepository implements UserRepositoryInterface
{
    /** @var PDO */
    protected $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function findByID(int $id): ?User
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
    public function findByEmail(string $email): ?User
    {
        try {
            $sth = $this->conn->prepare("SELECT * FROM `user` WHERE email=?");
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
    public function findAll(int $limit, int $offset): array
    {
        try {
            $sth = $this->conn->prepare("SELECT * FROM `user`");
            $sth->execute();
            $sth->setFetchMode(PDO::FETCH_CLASS, User::class);

            $users = [];
            while ($user = $sth->fetch()) {
                $users[] = $user;
            }

            return $users;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        return false;
    }
}
