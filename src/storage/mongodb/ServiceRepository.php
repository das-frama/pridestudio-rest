<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Service;
use app\domain\service\ServiceRepositoryInterface;
use app\storage\mongodb\base\AbstractRepository;
use MongoDB\Client;

class ServiceRepository extends AbstractRepository implements ServiceRepositoryInterface
{
    public function __construct(Client $client)
    {
        parent::__construct(getenv('DB_DATABASE'), 'services', $client);
        $this->defaultOptions = [
            'typeMap' => [
                'root' => Service::class,
                'document' => 'array',
            ],
        ];
    }
    
    /**
     * {@inheritDoc}
     */
    public function init(): bool
    {
        // Create schema validation.
        return $this->createSchemaValidation('services', [
            'name' => ['bsonType' => 'string'],
            'children' => ['bsonType' => 'array'],
            'sort' => ['bsonType' => 'int'],
            'is_active' => ['bsonType' => 'bool'],
            'updated_at' => ['bsonType' => 'int'],
            'created_by' => ['bsonType' => 'objectId'],
            'updated_by' => ['bsonType' => 'objectId'],
        ], ['name', 'is_active']);
    }
}
