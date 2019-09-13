<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Record;
use app\entity\Reservation;
use app\domain\record\RecordRepositoryInterface;
use MongoDB\Collection;
use MongoDB\Client;

/**
 * Class RecordRepository
 * @package app\storage\mongodb
 */
class RecordRepository implements RecordRepositoryInterface
{
    use RepositoryTrait;

    /** @var Collection */
    private $collection;

    /** @var array */
    private $defaultOptions = [];

    public function __construct(Client $client)
    {
        $this->collection = $client->selectDatabase('pridestudio')->selectCollection('records');
        $this->defaultOptions = [
            'typeMap' => [
                'root' => Record::class,
                'document' => 'array',
                'fieldPaths' => [
                    'reservations.$' => Reservation::class,
                ]
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function findOne(array $filter, array $include = []): ?Record
    {
        return $this->internalFindOne($filter, $this->defaultOptions, $include);
    }

    /**
     * Find all records from storage.
     * @param array $filter
     * @param array $include
     * @return Record[]
     */
    public function findAll(array $filter = [], array $include = []): array
    {
        return $this->internalFindAll($filter, $this->defaultOptions, $include);
    }

    /**
     * Find nested reservations in records.
     * @param array $filter
     * @return Reservation[]
     */
    public function findReservations(array $filter): array
    {
        $options = $this->defaultOptions;
        $options['projection'] = ['reservations' => 1];
        $cursor = $this->collection->find($this->convertFilter($filter), $options);
        return array_map(function (Record $record) {
            return $record->reservations;
        }, $cursor->toArray());
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        return false;
    }
}
