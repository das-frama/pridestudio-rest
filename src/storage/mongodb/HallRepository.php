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

    public function findByID(string $id): ?Hall
    {
        $hall = $this->collection->findOne([
            '_id' => new ObjectId($id)
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
     * @param int $limit
     * @param int $offset
     * @return Hall[]
     */
    public function findAll(int $limit, int $offset): array
    {
        return [];
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        return false;
    }
}
