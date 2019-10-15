<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\User;
use app\domain\user\UserRepositoryInterface;
use app\storage\mongodb\base\AbstractRepository;
use MongoDB\Client;

/**
 * Class UserRepository
 * @package app\storage\mongodb
 */
class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct(getenv('DB_DATABASE'), 'users', $client);
        $this->defaultOptions = [
            'typeMap' => [
                'root' => User::class,
                'document' => 'array',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function init(): bool
    {
        if (!$this->hasIndex('email')) {
            $this->collection->createIndex(['email' => 1], ['unique' => true]);
        }
        return $this->createSchemaValidation('users', [
            'email' => ['bsonType' => 'string'],
            'name' => ['bsonType' => 'string'],
            'phone' => ['bsonType' => 'string'],
            'password_hash' => ['bsonType' => 'string'],
            'role' => [
                'enum' => ['user', 'manager', 'admin', 'super'],
                'description' => 'can only be one of the enum values and is required',
            ],
            'is_active' => ['bsonType' => 'bool'],
            'updated_at' => ['bsonType' => 'int64'],
            'created_by' => ['bsonType' => 'objectId'],
            'updated_by' => ['bsonType' => 'objectId'],
        ], ['email', 'name', 'password_hash', 'role', 'is_active']);
    }
}
