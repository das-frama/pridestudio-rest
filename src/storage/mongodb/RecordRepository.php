<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Record;
use app\domain\record\RecordRepositoryInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Client;

/**
 * Class RecordRepository
 * @package app\storage\mongodb
 */
class RecordRepository implements RecordRepositoryInterface
{
    /** @var Collection */
    private $collection;

    public function __construct(Client $client)
    {
        $this->collection = $client->selectDatabase('pridestudio')->selectCollection('records');
    }

    public function findByID(string $id): ?Record
    {
        $record = $this->collection->findOne([
            '_id' => new ObjectId($id)
        ], [
            'typeMap' => [
                'root' => Record::class,
            ]
        ]);

        if ($record instanceof Record) {
            return $record;
        }

        return null;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return Record[]
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
