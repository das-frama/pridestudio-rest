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
     * Find a hall from storage by id.
     * @param string $id
     * @param bool $onlyActive
     * @param array $include
     * @param array $exclude
     * @return Hall|null
     */
    public function findByID(string $id, bool $onlyActive = true, array $include = [], array $exclude = []): ?Hall
    {
        $filter = ['_id' => new ObjectId($id)];
        if (isset($include['services_object']) && $include['services_object'] == 1) {
            return $this->findWithServices($filter);
        }

        return $this->findByFilter($filter, $onlyActive, $include, $exclude);
    }

    /**
     * Find a hall from storage by slug.
     * @param string $slug
     * @param bool $onlyActive
     * @param array $include
     * @param array $exclude
     * @return Hall|null
     */
    public function findBySlug(string $slug, bool $onlyActive = true, array $include = [], array $exclude = []): ?Hall
    {
        $filter = ['slug' => $slug];
        if (in_array('services_object', $include)) {
            return $this->findWithServices($filter, $onlyActive, $include, $exclude);
        }

        return $this->findByFilter($filter, $onlyActive, $include, $exclude);
    }

    /**
     * Find a hall from storage with services.
     * @param array $filter
     * @param bool $onlyActive
     * @param array $include
     * @param array $exclude
     * @return Hall|null
     */
    public function findWithServices(array $filter, bool $onlyActive = true, $include = [], $exclude = []): ?Hall
    {
        if ($onlyActive) {
            $filter['is_active'] = true;
        }
        // Setup include.
        if (empty($include)) {
            $include = Hall::publicProperties();
        }
        if (!empty($exclude)) {
            $include = array_diff_key($include, $exclude);
        }
        // Setup project.
        $project = array_fill_keys($include, 1);
        $project['_id'] = 1;
        $project['services_object'] = [
            '_id' => 1,
            'name' => 1,
            'children' => [
                '$filter' => [
                    'input' => '$services_object.children',
                    'as' => 'child',
                    'cond' => ['in' => ['$$child._id', '$services.children']],
                ]
            ]
        ];
        // Setup groups.
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

        $array = $cursor->toArray();
        if (count($array) === 0) {
            return $this->findByFilter($filter, $onlyActive, $include, $exclude);
        }
        $hall = $array[0];
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
     * @param array $include
     * @param array $exclude
     * @return Hall[]
     */
    public function findServices(array $filter = [], bool $onlyActive = true, array $include = [], array $exclude = []): array
    {
        return [];
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
     * Find a hall from storage by condition.
     * @param array $filter
     * @param bool $onlyActive
     * @param array $include
     * @param array $exclude
     * @return Hall|null
     */
    private function findByFilter(array $filter, bool $onlyActive = false, array $include = [], array $exclude = []): ?Hall
    {
        if ($onlyActive) {
            $filter['is_active'] = true;
        }
        $options = $this->defaultOptions;
        if (!empty($include)) {
            $options['projection'] = array_fill_keys($include, 1);
        } elseif (!empty($exclude)) {
            $options['projection'] = array_fill_keys($exclude, 0);
        }

        $hall = $this->collection->findOne($filter, $options);
        if (!$hall instanceof Hall) {
            return null;
        }
        $hall->setInclude($include);
        $hall->setExclude($exclude);
        return $hall;
    }
}
