<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\User;
use app\domain\user\UserRepositoryInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

/**
 * Class UserRepository
 * @package app\storage\mongodb
 */
class UserRepository implements UserRepositoryInterface
{
    /** @var Collection */
    private $collection;

    public function __construct(Database $database)
    {
        $this->collection = $database->selectCollection('user');
    }

    public function findByID(string $id): ?User
    {
        $user = $this->collection->findOne([
            '_id' => new ObjectId($id)
        ], [
            'typeMap' => [
                'root' => User::class,
            ]
        ]);

        if ($user instanceof User) {
            return $user;
        }

        return null;
    }

    public function findByEmail(string $email): ?User
    {
        return null;
    }

    /**
     * @return User[]
     */
    public function findAll(int $limit, int $offset): array
    {
        return [];
    }
    public function save(): bool
    {
        return false;
    }
}
