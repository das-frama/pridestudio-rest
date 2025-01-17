<?php
declare(strict_types=1);

namespace App\Repositories\MongoDB;

use App\Entities\Client;
use App\Repositories\Base\AbstractRepository;
use App\Repositories\ClientRepositoryInterface;
use MongoDB\Client as MongoDBClient;

/**
 * Class ClientRepository
 * @package App\Repositories\MongoDB
 */
class ClientRepository extends AbstractRepository implements ClientRepositoryInterface
{
    /**
     * ClientRepository constructor.
     * @param MongoDBClient $client
     */
    public function __construct(MongoDBClient $client)
    {
        parent::__construct(getenv('DB_DATABASE'), 'clients', $client);
        $this->defaultOptions = [
            'typeMap' => [
                'root' => Client::class,
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
        if (!$this->hasIndex('phone')) {
            $this->collection->createIndex(['phone' => 1], ['unique' => true]);
        }
        return $this->createSchemaValidation('clients', [
            'name' => ['bsonType' => 'string'],
            'email' => ['bsonType' => 'string'],
            'phone' => ['bsonType' => 'string'],
            'sex' => ['bsonType' => 'int'],
            'comment' => ['bsonType' => 'string'],
            'updated_at' => ['bsonType' => 'int'],
        ], ['name', 'email']);
    }
}
