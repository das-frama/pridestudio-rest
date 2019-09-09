<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Hall;
use app\entity\Service;
use app\entity\ServiceChild;
use app\entity\PriceRule;
use app\domain\hall\HallRepositoryInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Client;

/**
 * Class HallRepository
 * @package app\storage\mongodb
 */
class HallRepository implements HallRepositoryInterface
{
    /** @var Collection */
    private $collection;

    /** @var array */
    private $defaultOptions;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->collection = $client->selectDatabase('pridestudio')->selectCollection('halls');
        $this->defaultOptions = [
            'typeMap' => [
                'root' => Hall::class,
                'document' => 'array',
                'fieldPaths' => [
                    'services_object.$' => Service::class,
                    'services_object.$.children.$' => ServiceChild::class,
                    'prices.$.price_rule' => PriceRule::class
                ]
            ],
        ];
    }

    /**
     * Find a hall from storage by filter.
     * @param array $filter
     * @param bool $onlyActive
     * @param array $include
     * @param array $exclude
     * @return Hall|null
     */
    public function findOne(array $filter, bool $onlyActive = false, array $include = [], array $exclude = []): ?Hall
    {
        // Prepare filter.
        if (isset($filter['id'])) {
            $filter['_id'] = new ObjectId($filter['id']);
            unset($filter['id']);
        }
        if ($onlyActive) {
            $filter['is_active'] = true;
        }
        // Prepare options.
        $options = $this->defaultOptions;
        if (!empty($include)) {
            $options['projection'] = array_fill_keys($include, 1);
            if (isset($options['projection']['id'])) {
                $options['projection']['_id'] = 1;
                unset($options['projection']['id']);
            }
        } elseif (!empty($exclude)) {
            $options['projection'] = array_fill_keys($exclude, 0);
        }
        // Process result.
        $hall = $this->collection->findOne($filter, $options);
        if (!$hall instanceof Hall) {
            return null;
        }
        $hall->setInclude($include);
        $hall->setExclude($exclude);

        return $hall;
    }

    /**
     * Find a hall from storage with services.
     * @param array $filter
     * @param bool $onlyActive
     * @param array $include
     * @param array $exclude
     * @return Hall|null
     */
    public function findWithServices(array $filter, bool $onlyActive = true, array $include = [], array $exclude = []): ?Hall
    {
        // Prepare filter.
        if ($onlyActive) {
            $filter['is_active'] = true;
        }
        // Prepare include.
        if (empty($include)) {
            $include = Hall::publicProperties();
        }
        if (!empty($exclude)) {
            $include = array_diff_key($include, $exclude);
        }
        // Prepare project.
        $project = array_fill_keys($include, 1);
        unset($project['id']);
        $project['_id'] = 1;
        $project['services_object'] = [
            '_id' => 1,
            'name' => 1,
            'children' => [
                '$filter' => [
                    'input' => '$services_object.children',
                    'as' => 'child',
                    'cond' => ['$in' => ['$$child._id', '$services.children']],
                ]
            ]
        ];
        // Prepare groups.
        $group = [];
        foreach ($include as $column) {
            if ($column != 'id') {
                $group[$column] = ['$first' => '$' . $column];
            }
        }
        $group['_id'] = '$_id';
        if (in_array('services', $include)) {
            $group['services'] = ['$push' => '$services'];
        }
        $group['services_object'] = ['$push' => '$services_object'];

        // Perform query.
        $cursor = $this->collection->aggregate([
            ['$match' => $filter],
            ['$limit' => 1],
            ['$unwind' => '$services'],
            ['$lookup' => [
                'from' => 'services',
                'localField' => 'services.category_id',
                'foreignField' => '_id',
                'as' => 'services_object'
            ]],
            ['$unwind' => '$services_object'],
            ['$project' => $project],
            ['$group' => $group]
        ], $this->defaultOptions);

        // Process result.
        $halls = $cursor->toArray();
        if (count($halls) === 0) {
            return $this->findOne($filter, $onlyActive, $include, $exclude);
        }
        $hall = $halls[0];
        if ($hall instanceof Hall) {
            $hall->setInclude($include);
            foreach ($hall->services_object as $service) {
                $service->setInclude(['id', 'name', 'children']);
            }
        }

        return $hall;
    }

    /**
     * Find services from storage in hall.
     * @param array $filter
     * @param bool $onlyActive
     * @param array $selected
     * @param array $include
     * @param array $exclude
     * @return Service[]
     */
    public function findServices(array $filter = [], bool $onlyActive = true, array $selected = [], array $include = [], array $exclude = []): array
    {
        // Prepare filter.
        if ($onlyActive) {
            $filter['is_active'] = true;
        }
        // Prepare include.
        if (!empty($selected)) {
            $selected = array_map(function ($id) {
                return new ObjectId($id);
            }, $selected);
        }
        if (empty($include)) {
            $include = Service::publicProperties();
        }
        if (!empty($exclude)) {
            $include = array_diff($include, $exclude);
        }
        // Prepare project.
        $project = [
            'services' => 1,
            'services_object' => array_fill_keys($include, 1),
        ];
        if (isset($project['services_object']['id'])) {
            $project['services_object']['_id'] = 1;
            unset($project['services_object']['id']);
        }
        $project['services_object']['children'] = [
            '$filter' => [
                'input' => '$services_object.children',
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
        // Perform query.
        $cursor = $this->collection->aggregate([
            ['$match' => $filter],
            ['$limit' => 1],
            ['$unwind' => '$services'],
            ['$lookup' => [
                'from' => 'services',
                'localField' => 'services.category_id',
                'foreignField' => '_id',
                'as' => 'services_object'
            ]],
            ['$unwind' => '$services_object'],
            ['$project' => $project],
            ['$match' => [
                '$or' => [
                    ['services.children' => ['$in' => $selected]],
                    ['services.parents' => ['$in' => $selected]],
                ]
            ]],
            ['$replaceRoot' => ['newRoot' => '$services_object']]
        ], $options);

        // Read and return result.
        $services = $cursor->toArray();
        if (count($services) == 0) {
            return [];
        }
        foreach ($services as $service) {
            if ($service instanceof Service) {
                $service->setInclude($include);
                $service->setExclude($exclude);
            }
        }

        return $services;
    }

    /**
     * Find all halls from storage.
     * @param int $limit
     * @param int $offset
     * @param bool $onlyActive
     * @param array $include
     * @return Hall[]
     */
    public function findAll(int $limit, int $offset, bool $onlyActive = true, array $include = [], array $exclude = []): array
    {
        $filter = [];
        if ($onlyActive) {
            $filter['is_active'] = true;
        }
        $options = $this->defaultOptions;
        if ($limit > 0) {
            $options['limit'] = $limit;
        }
        if (!empty($include)) {
            $options['projection'] = array_fill_keys($include, 1);
        }
        $result = [];
        $cursor = $this->collection->find($filter, $options);
        foreach ($cursor as $hall) {
            if ($hall instanceof Hall) {
                $hall->setInclude($include);
                $result[] = $hall;
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        return false;
    }

    /**
     * Check if document exists.
     * @param array $filter
     * @param bool $onlyActive
     * @return bool
     */
    public function isExists(array $filter, bool $onlyActive = true): bool
    {
        if (isset($filter['id'])) {
            $filter['_id'] = new ObjectId($filter['id']);
            unset($filter['id']);
        }
        if ($onlyActive) {
            $filter['is_active'] = true;
        }

        return (bool) $this->collection->count($filter, []);
    }
}
