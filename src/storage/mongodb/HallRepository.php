<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Hall;
use app\entity\Service;
use app\entity\ServiceChild;
use app\entity\PriceRule;
use app\domain\hall\HallRepositoryInterface;
use MongoDB\Client;

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
    public function findAll(array $filter, array $include = []): array
    {
        return $this->internalFindAll($filter, $this->defaultOptions, $include);
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
            'name' => 'string',
            'slug' => 'string',
            'description' => 'string',
            'base_price' => 'int',
            'preview_image' => 'string',
            'detail_image' => 'string',
            'services' => 'array',
            'prices' => 'array',
            'sort' => 'int',
            'is_active' => 'bool',
            'updated_at' => 'int',
            'created_by' => 'objectId',
            'updated_by' => 'objectId',
        ], ['name', 'slug', 'base_price', 'sort', 'is_active']);
    }
}
