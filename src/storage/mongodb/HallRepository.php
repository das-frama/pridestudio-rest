<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Hall;
use app\entity\Service;
use app\entity\ServiceChild;
use app\entity\PriceRule;
use app\domain\hall\HallRepositoryInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;

/**
 * Class HallRepository
 * @package app\storage\mongodb
 */
class HallRepository extends Repository implements HallRepositoryInterface
{
    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, 'halls');
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

    public function findOne(array $filter, array $include = [], array $exclude = []): ?Hall
    {
        $hall = $this->internalFindOne($filter, $include, $exclude);
        if ($hall === null) {
            return null;
        }
        return $hall;
    }

    /**
     * Find services from storage in hall.
     * @param array $filter
     * @param array $selected
     * @param array $include
     * @param array $exclude
     * @return Service[]
     */
    public function findServices(array $filter = [], array $selected, array $include = [], array $exclude = []): array
    {
        // Prepare include.
        if (empty($include)) {
            $include = Service::publicProperties();
        }
        if (!empty($exclude)) {
            $include = array_diff($include, $exclude);
        }
        $selectedObjectID = [];
        foreach ($selected as $id) {
            $selectedObjectID[] = new ObjectId($id);
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
                    ['services.children' => ['$in' => $selectedObjectID]],
                    ['services.parents' => ['$in' => $selectedObjectID]],
                ]
            ]],
            ['$replaceRoot' => ['newRoot' => '$services_join']]
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
