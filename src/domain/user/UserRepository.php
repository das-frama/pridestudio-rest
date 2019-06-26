<?php declare(strict_type=1);
namespace app\domain\user;

interface UserRepository
{
    public function findByID(int $id) : ?User;
    public function findByEmail(string $email) : ?User;
    /**
     * @return User[]
     */
    public function findAll(int $limit, int $offset) : array;
    public function save() : bool;
}
