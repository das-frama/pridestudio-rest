<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Hall;
use app\entity\Service;
use app\entity\ServiceChild;
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
        return $this->findByCondition(['_id' => new ObjectId($id)], $onlyActive, $include, $exclude);
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
        return $this->findByCondition(['slug' => $slug], $onlyActive, $include, $exclude);
    }

    /** 
     * Find hall and join with services.
     * @param string $id
     * @return Hall|null
     */
    public function findWithServices(string $id): ?Hall
    {
        $cursor = $this->collection->aggregate([
            ['$match' => ['_id' => new ObjectId($id)]],
            ['$limit' => 1],
            ['$unwind' => '$services'],
            [
                '$lookup' => [
                    'from' => 'services',
                    'localField' => 'services.category_id',
                    'foreignField' => '_id',
                    'as' => 'services_object'
                ]
            ],
            ['$unwind' => '$services_object'],
            [
                '$project' => [
                    '_id' => 1,
                    'name' => 1,
                    'base_price' => 1,
                    'services' => 1,
                    'services_object' => [
                        '_id' => 1,
                        'name' => 1,
                        'children' => [
                            '$filter' => [
                                'input' => '$services_object.children',
                                'as' => 'child',
                                'cond' => ['in' => ['$$child._id', '$services.children']],
                            ]
                        ],
                    ],
                    'prices' => 1,
                ]
            ],
            [
                '$group' => [
                    '_id' => '$_id',
                    'name' => ['$first' => '$name'],
                    'base_price' => ['$first' => '$base_price'],
                    'prices' => ['$first' => '$prices'],
                    'services' => ['$push' => '$services'],
                    'services_object' => ['$push' => '$services_object'],
                ]
            ]
        ], $this->defaultOptions);

        $array = $cursor->toArray();
        if (count($array) === 0) {
            return null;
        }
        return $array[0];
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
     * @param array $condition
     * @param bool $onlyActive
     * @param array $include
     * @param array $exclude
     * @return Hall|null
     */
    private function findByCondition(array $condition, bool $onlyActive = false, array $include = [], array $exclude = []): ?Hall
    {
        $filter = $condition;
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
