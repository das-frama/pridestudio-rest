<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Hall;
use app\entity\HallService;
use app\domain\hall\HallRepositoryInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Client;

/**
 * Class ServiceRepository
 * @package app\storage\mongodb
 */
class ServiceRepository implements HallRepositoryInterface
{
    /** @var Collection */
    private $collection;

    /** @var array */
    private $defaultOptions;

    public function __construct(Client $client)
    {
        $this->collection = $client->selectDatabase('pridestudio')->selectCollection('services');
        $this->defaultOptions = [
            'typeMap' => [
                'root' => Hall::class,
                'document' => 'array',
                'fieldPaths' => [
                    'services.$' => HallService::class,
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