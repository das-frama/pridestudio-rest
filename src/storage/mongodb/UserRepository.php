<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\User;
use app\domain\user\UserRepositoryInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Client;

/**
 * Class UserRepository
 * @package app\storage\mongodb
 */
class UserRepository implements UserRepositoryInterface
{
    /** @var Collection */
    private $collection;

    public function __construct(Client $client)
    {
        $this->collection = $client->selectDatabase('pridestudio')->selectCollection('user');
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

    /**
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return null;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return User[]
     */
    public function findAll(int $limit, int $offset): array
    {
        return [];
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        return false;
    }
}
