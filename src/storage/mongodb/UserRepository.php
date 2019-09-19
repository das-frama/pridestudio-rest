<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\User;
use app\domain\user\UserRepositoryInterface;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

/**
 * Class UserRepository
 * @package app\storage\mongodb
 */
class UserRepository implements UserRepositoryInterface
{
    use RepositoryTrait;

    /**
     * UserRepository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        // Inside repository trait.
        $this->database = $client->selectDatabase('pridestudio');
        $this->collection = $this->database->selectCollection('users');
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
    public function findOne(array $filter, array $include = []): ?User
    {
        return $this->internalFindOne($filter, $this->defaultOptions, $include);
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(array $filter, array $include = []): array
    {
        return $this->internalFindAll($filter, $this->defaultOptions, $include);
    }

    /**
     * {@inheritDoc}
     */
    public function insert(User $user): ?string
    {
        $result = $this->collection->insertOne($user, [
            'bypassDocumentValidation' => true,
        ]);
        $id = $result->getInsertedId();
        return ($id instanceof ObjectId) ? (string) $id : null;
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
            'email' => 'string',
            'name' => 'string',
            'phone' => 'string',
            'password_hash' => 'string',
            'role' => 'string',
            'is_active' => 'bool',
            'updated_at' => 'int',
            'created_by' => 'objectId',
            'updated_by' => 'objectId',
        ], ['email', 'name', 'password_hash', 'role', 'is_active']);
    }

    /**
     * {@inheritDoc}
     */
    public function isExists(array $filter): bool
    {
        return (bool) $this->collection->count($this->convertFilter($filter));
    }
}
