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

    public function __construct(Client $client)
    {
        $this->collection = $client->selectDatabase('pridestudio')->selectCollection('halls');
    }

    /**
     * Find a hall from storage by id.
     * @param string $slug
     * @return Hall|null
     */
    public function findByID(string $slug): ?Hall
    {
        $hall = $this->collection->findOne([
            '_id' => new ObjectId($slug)
        ], [
            'typeMap' => [
                'root' => Hall::class,
                'document' => 'array',
            ]
        ]);

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
        $options = [
            'typeMap' => [
                'root' => Hall::class,
                'document' => 'array',
            ],
        ];
        if (!empty($include)) {
            $options['projection'] = array_fill_keys($include, 1);
        }

        $hall = $this->collection->findOne($filter, $options);
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

        $options = [
            'typeMap' => [
                'root' => Hall::class,
                'document' => 'array',
            ]
        ];
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
}
