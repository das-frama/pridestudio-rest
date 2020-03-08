<?php
declare(strict_types=1);

namespace App\Repositories\MongoDB;

use App\Entities\Service;
use App\Entities\ServiceChild;
use App\Repositories\Base\AbstractRepository;
use App\Repositories\ServiceRepositoryInterface;
use MongoDB\Client;

/**
 * Class ServiceRepository
 * @package App\Repositories\MongoDB
 */
class ServiceRepository extends AbstractRepository implements ServiceRepositoryInterface
{
    /**
     * ServiceRepository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct(getenv('DB_DATABASE'), 'services', $client);
        $this->defaultOptions = [
            'typeMap' => [
                'root' => Service::class,
                'document' => 'array',
                'fieldPaths' => [
                    'children.$' => ServiceChild::class
                ]
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
