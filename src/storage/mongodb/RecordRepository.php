<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Record;
use app\entity\Reservation;
use app\domain\record\RecordRepositoryInterface;
use app\storage\mongodb\base\AbstractRepository;
use MongoDB\Client;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Class RecordRepository
 * @package app\storage\mongodb
 */
class RecordRepository extends AbstractRepository implements RecordRepositoryInterface
{
    public function __construct(Client $client)
    {
        parent::__construct(getenv('DB_DATABASE'), 'records', $client);
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
    public function init(): bool
    {
        // Create schema validation.
        return $this->createSchemaValidation('records', [
            'client_id' => ['bsonType' => 'objectId'],
            'hall_id' => ['bsonType' => 'objectId'],
            'reservations' => ['bsonType' => 'array'],
            'service_ids' => ['bsonType' => 'array'],
            'payment_id' => ['bsonType' => 'objectId'],
            'coupon_id' => ['bsonType' => 'objectId'],
            'total' => ['bsonType' => 'int'],
            'comment' => ['bsonType' => 'string'],
            'status' => ['bsonType' => 'int'],
            'updated_at' => ['bsonType' => 'int'],
            'created_by' => ['bsonType' => 'objectId'],
            'updated_by' => ['bsonType' => 'objectId'],
        ], ['client_id', 'hall_id', 'reservations', 'status']);
    }

    /**
     * {@inheritDoc}
     */
    public function findReservations(array $filter): array
    {
        $options = $this->defaultOptions;
        $options['projection'] = ['reservations' => 1];
        $cursor = $this->collection->find($this->convertFilter($filter), $options);
        $records = $cursor->toArray();
        $result = [];
        foreach ($cursor as $record) {
            $result = array_merge($result, (array) $record->reservations);
        }
        return $result;
    }
}
