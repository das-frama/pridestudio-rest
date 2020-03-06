<?php

declare(strict_types=1);

namespace App\Storage\MongoDB;

use App\Domain\Hall\HallRepositoryInterface;
use App\Entity\Hall;
use App\Entity\HallService;
use App\Entity\PriceRule;
use App\Entity\Service;
use App\Entity\ServiceChild;
use App\Storage\MongoDB\Base\AbstractRepository;
use MongoDB\Client;

/**
 * Class HallRepository
 * @package App\Storage\MongoDB
 */
class HallRepository extends AbstractRepository implements HallRepositoryInterface
{
    public function __construct(Client $client)
    {
        parent::__construct(getenv('DB_DATABASE'), 'halls', $client);
        $this->defaultOptions = [
            'typeMap' => [
                'root' => Hall::class,
                'document' => 'array',
                'fieldPaths' => [
                    'services.$' => HallService::class,
                    'prices.$' => PriceRule::class,
                ]
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function init(): bool
    {
        // Create index.
        if (!$this->hasIndex('slug')) {
            $this->collection->createIndex(['slug' => 1], ['unique' => true]);
        }
        // Create schema validation.
        return $this->createSchemaValidation('halls', [
            'name' => ['bsonType' => 'string'],
            'slug' => ['bsonType' => 'string'],
            'description' => ['bsonType' => 'string'],
            'base_price' => ['bsonType' => 'int'],
            'preview_image' => ['bsonType' => 'string'],
            'detail_image' => ['bsonType' => 'string'],
            'services' => ['bsonType' => 'array'],
            'prices' => ['bsonType' => 'array'],
            'sort' => ['bsonType' => 'int'],
            'is_active' => ['bsonType' => 'bool'],
            'updated_at' => ['bsonType' => 'int'],
            'created_by' => ['bsonType' => 'objectId'],
            'updated_by' => ['bsonType' => 'objectId'],
        ], ['name', 'slug', 'sort', 'is_active']);
    }

    /**
     * {@inheritDoc}
     */
    public function findServices(array $filter, array $selected, array $include = []): array
    {
        // Prepare include.
        if (empty($include)) {
            $include = Service::publicProperties();
        }
        // Prepare project.
        $project = [
            'services' => 1,
            'services_join' => array_fill_keys($include, 1),
        ];
        if (isset($project['services_join']['id'])) {
            $project['services_join']['_id'] = 1;
            unset($project['services_join']['id']);
        }
        $project['services_join']['children'] = [
            '$filter' => [
                'input' => '$services_join.children',
                'as' => 'child',
                'cond' => ['$in' => ['$$child._id', '$services.children']]
            ]
        ];
        // Prepare options.
        $options = [
            'typeMap' => [
                'root' => Service::class,
                'document' => 'array',
                'fieldPaths' => [
                    'children.$' => ServiceChild::class,
                ]
            ]
        ];
        // Confert selected to array of ObjectId
        $objectIDs = $this->convertToObjectId($selected);
        // Perform query.
        $cursor = $this->collection->aggregate([
            ['$match' => $this->convertFilter($filter)],
            ['$limit' => 1],
            ['$unwind' => '$services'],
            ['$lookup' => [
                'from' => 'services',
                'localField' => 'services.category_id',
                'foreignField' => '_id',
                'as' => 'services_join'
            ]],
            ['$unwind' => '$services_join'],
            ['$project' => $project],
            ['$match' => [
                '$or' => [
                    ['$and' => [
                        ['services.children' => ['$in' => $objectIDs]],
                        ['services.parents' => ['$eq' => []]]
                    ]],
                    ['services.parents' => ['$in' => $objectIDs]],
                ]
            ]],
            ['$replaceRoot' => ['newRoot' => '$services_join']]
        ], $options);

        // Read and return result.
        return array_map(function (Service $service) use ($include) {
            $service->setInclude($include);
            return $service;
        }, $cursor->toArray());
    }
}
