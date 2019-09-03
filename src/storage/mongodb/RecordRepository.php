<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Record;
use app\domain\record\RecordRepositoryInterface;
use app\entity\Reservation;
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

    /** @var array */
    private $options = [];

    public function __construct(Client $client)
    {
        $this->collection = $client->selectDatabase('pridestudio')->selectCollection('records');
        $this->options = [
            'typeMap' => [
                'root' => Record::class,
                'document' => 'array',
            ]
        ];
    }

    /**
     * Find a record from storage by id.
     * @param string $id
     * @return Record|null
     */
    public function findByID(string $id): ?Record
    {
        $record = $this->collection->findOne(['_id' => new ObjectId($id)], $this->options);
        if ($record instanceof Record) {
            return $record;
        }

        return null;
    }

    /**
     * Find all records from storage.
     * @param int $limit
     * @param int $offset
     * @return Record[]
     */
    public function findAll(int $limit, int $offset): array
    {
        return [];
    }

    /**
     * Find nested reservations in records.
     * @param array $filter
     * @return Reservation[]
     */
    public function findReservations(array $filter): array
    {
        $result = [];
        $this->options['projection'] = ['reservations' => 1];
        $cursor = $this->collection->find($filter, $this->options);
        foreach ($cursor as $record) {
            if ($record instanceof Record) {
                $result = array_merge($result, $record->reservations);
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
