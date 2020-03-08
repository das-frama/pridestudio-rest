<?php
declare(strict_types=1);

namespace App\Repositories\MongoDB;

use App\Entities\Client;
use App\Entities\Payment;
use App\Entities\Record;
use App\Entities\Reservation;
use App\Repositories\Base\AbstractRepository;
use App\Repositories\ClientRepositoryInterface;
use App\Repositories\RecordRepositoryInterface;
use MongoDB\Client as MongoDBClient;

/**
 * Class RecordRepository
 * @package App\Repositories\MongoDB
 */
class RecordRepository extends AbstractRepository implements RecordRepositoryInterface
{
    /**
     * RecordRepository constructor.
     * @param MongoDBClient $client
     */
    public function __construct(MongoDBClient $client)
    {
        parent::__construct(getenv('DB_DATABASE'), 'records', $client);
        $this->defaultOptions = [
            'typeMap' => [
                'root' => Record::class,
                'document' => 'array',
                'fieldPaths' => [
                    'reservations.$' => Reservation::class,
                    'payment' => Payment::class,
                ],
            ],
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

    public function findOne(array $filter, array $with = []): ?Record
    {
        $record = parent::findOne($filter, $with);
        if (!($record instanceof Record)) {
            return null;
        }
        if (isset($with['client']) && $with['client'] instanceof ClientRepositoryInterface) {
            $client = $with['client']->findOne(['id' => $record->client_id]);
            if ($client instanceof Client) {
                $record->client = $client;
            }
        }

        return $record;
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

//    /**
//     * @param Record $record
//     * @param array $with
//     * @return Record
//     */
//    protected function withRelations(Record $record, array $with): Record
//    {
//        $newRecord = clone $record;
//        foreach ($with as $relation) {
//            if (!isset($this->relations[$relation]) || !property_exists($newRecord, $relation)) {
//                continue;
//            }
//            list($property, $collection) = $this->relations[$relation];
//            if (is_array($newRecord->{$property})) {
//                $newRecord->{$relation} = $this->database->{$collection}->find([
//                    '_id' => $this->convertToObjectId($newRecord->{$property}),
//                ])->toArray();
//            } else {
//                $newRecord->{$relation} = $this->database->{$collection}->findOne([
//                    '_id' => new ObjectId($newRecord->{$property}),
//                ]);
//            }
//        }
//
//        return $newRecord;
//    }
}
