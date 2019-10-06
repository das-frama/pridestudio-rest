<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Hall;
use app\entity\Service;
use app\entity\ServiceChild;
use app\entity\PriceRule;
use app\domain\hall\HallRepositoryInterface;
use MongoDB\Client;
use MongoDB\BSON\Regex;
use MongoDB\BSON\ObjectId;

/**
 * Class HallRepository
 * @package app\storage\mongodb
 */
class HallRepository implements HallRepositoryInterface
{
    use RepositoryTrait;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        // Inside repository trait.
        $this->database = $client->selectDatabase('pridestudio');
        $this->collection = $this->database->selectCollection("halls");
        $this->defaultOptions = [
            'typeMap' => [
                'root' => Hall::class,
                'document' => 'array',
                'fieldPaths' => [
                    'prices.$.price_rule' => PriceRule::class
                ]
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function findOne(array $filter, array $include = []): ?Hall
    {
        return $this->internalFindOne($filter, $this->defaultOptions, $include);
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
            ['$match' => $filter],
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
                        ['services.parents' => ['$exists' => false]]
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

    /**
     * {@inheritDoc}
     */
    public function findAll(array $filter, int $limit = 0, int $skip = 0, array $sort = [], array $include = []): array
    {
        $options = array_merge($this->defaultOptions, [
            'limit' => $limit,
            'skip' => $skip,
            'sort' => $sort,
        ]);
        return $this->internalFindAll($filter, $options, $include);
    }

    /**
     * {@inheritDoc}
     */
    public function search(array $search, int $limit = 0, int $skip = 0, array $sort = [], array $include = []): array
    {
        $options = array_merge($this->defaultOptions, [
            'limit' => $limit,
            'skip' => $skip,
            'sort' => $sort,
        ]);
        $filter = array_map(function ($value) {
            $str = (string) $value;
            $first = substr($value, 0, 1);
            $last = substr($value, -1);
            if ($first === '%' && $last === '%') {
                $str = substr($str, 1, -1);
            } elseif ($last === '%') {
                $str = '^' . substr($str, 0, -1);
            } elseif ($first === '%') {
                $str = substr($str, 1) . '$';
            } else {
                return $str;
            }
            return new Regex($str, 'i');
        }, $search);
        return $this->internalFindAll(['$or' => $filter], $options, $include);
    }

    /**
     * {@inheritDoc}
     */
    public function count(array $filter = []): int
    {
        return $this->collection->count($this->convertFilter($filter));
    }

    /**
     * {@inheritDoc}
     */
    public function save(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isExists(array $filter): bool
    {
        return (bool) $this->collection->count($this->convertFilter($filter));
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
            'updated_at' => ['bsonType' => 'long'],
            'created_by' => ['bsonType' => 'objectId'],
            'updated_by' => ['bsonType' => 'objectId'],
        ], ['name', 'slug', 'base_price', 'sort', 'is_active']);
    }

    /**
     * {@inheritDoc}
     */
    public function insert(Hall $hall): ?string
    {
        $result = $this->collection->insertOne($hall, [
            'bypassDocumentValidation' => true,
        ]);
        $id = $result->getInsertedId();
        return ($id instanceof ObjectId) ? (string) $id : null;
    }

    /**
     * {@inheritDoc}
     */
    public function update(Hall $hall): bool
    {
        if ($hall->id === null) {
            return false;
        }
        $filter = ['_id' => new ObjectId($hall->id)];
        $update = [
            '$set' => $hall
        ];
        $result = $this->collection->updateOne($filter, $update, [
            'bypassDocumentValidation' => true,
        ]);
        return $result->getModifiedCount() > 0;
    }
}
