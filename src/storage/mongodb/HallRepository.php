<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Hall;
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
    private $options;

    public function __construct(Client $client)
    {
        $this->collection = $client->selectDatabase('pridestudio')->selectCollection('halls');
        $this->options = [
            'typeMap' => [
                'root' => Hall::class,
                'document' => 'array',
            ],
        ];
    }

    /**
     * Find a hall from storage by id.
     * @param string $id
     * @return Hall|null
     */
    public function findByID(string $id): ?Hall
    {
        $hall = $this->collection->findOne(['_id' => new ObjectId($id)], $this->options);
        if ($hall instanceof Hall) {
            return $hall;
        }

        return null;
    }

    /**
     * Find a hall from storage by slug.
     * @param string $slug
     * @param bool $onlyActive
     * @param array $include
     * @return Hall|null
     */
    public function findBySlug(string $slug, bool $onlyActive, array $include): ?Hall
    {
        $filter = ['slug' => $slug];
        if ($onlyActive) {
            $filter['is_active'] = true;
        }
        if (!empty($include)) {
            $this->options['projection'] = array_fill_keys($include, 1);
        }

        $hall = $this->collection->findOne($filter, $this->options);
        if ($hall instanceof Hall) {
            $hall->setInclude($include);
            return $hall;
        }

        return null;
    }

    /**
     * Find all halls from storage.
     * @param int $limit
     * @param int $offset
     * @param bool $onlyActive
     * @param array $include
     * @return Hall[]
     */
    public function findAll(int $limit, int $offset, bool $onlyActive, array $include): array
    {
        $filter = [];
        if ($onlyActive) {
            $filter['is_active'] = true;
        }
        if ($limit > 0) {
            $this->options['limit'] = $limit;
        }
        if (!empty($include)) {
            $this->options['projection'] = array_fill_keys($include, 1);
        }

        $result = [];
        $cursor = $this->collection->find($filter, $this->options);
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
}
