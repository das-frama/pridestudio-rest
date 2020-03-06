<?php

declare(strict_types=1);

namespace App\Storage\MongoDB;

use App\Domain\Record\RecordRepositoryInterface;
use App\Entity\Payment;
use App\Entity\Record;
use App\Entity\Reservation;
use App\Storage\MongoDB\Base\AbstractRepository;
use MongoDB\Client;

/**
 * Class RecordRepository
 * @package App\Storage\MongoDB
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
                    'payment' => Payment::class,
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
            'payment' => ['bsonType' => 'object'],
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
        $result = [];
        foreach ($cursor as $record) {
            $result = array_merge($result, (array)$record->reservations);
        }
        return $result;
    }
}
