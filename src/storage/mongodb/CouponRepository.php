<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Coupon;
use app\domain\record\CouponRepositoryInterface;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

/**
 * Class CouponRepository
 * @package app\storage\mongodb
 */
class CouponRepository implements CouponRepositoryInterface
{
    use RepositoryTrait;

    /**
     * CouponRepository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        // Inside repository trait.
        $this->database = $client->selectDatabase('pridestudio');
        $this->collection = $this->database->selectCollection('coupons');
        $this->defaultOptions = [
            'typeMap' => [
                'root' => Coupon::class,
                'document' => 'array',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function findOne(array $filter, array $include = []): ?Coupon
    {
        return $this->internalFindOne($filter, $this->defaultOptions, $include);
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(array $filter = [], array $include = []): array
    {
        return $this->internalFindAll($filter, $this->defaultOptions, $include);
    }

    /**
     * {@inheritDoc}
     */
    public function insert(Coupon $coupon): ?string
    {
        $result = $this->collection->insertOne($coupon, [
            'bypassDocumentValidation' => true,
        ]);
        $id = $result->getInsertedId();
        return ($id instanceof ObjectId) ? (string) $id : null;
    }

    /**
     * {@inheritDoc}
     */
    public function init(): bool
    {
        if (!$this->hasIndex('code')) {
            $this->collection->createIndex(['code' => 1], ['unique' => true]);
        }
        return $this->createSchemaValidation('coupons', [
            'code' => ['bsonType' => 'string'],
            'factor' => ['bsonType' => 'double'],
            'length' => ['bsonType' => 'int'],
            'start_at' => ['bsonType' => 'long'],
            'end_at' => ['bsonType' => 'long'],
            'sort' => ['bsonType' => 'int'],
            'is_active' => ['bsonType' => 'bool'],
            'updated_at' => ['bsonType' => 'long'],
            'created_by' => ['bsonType' => 'objectId'],
            'updated_by' => ['bsonType' => 'objectId'],
        ], ['code', 'factor', 'is_active']);
    }

    /**
     * {@inheritDoc}
     */
    public function isExists(array $filter): bool
    {
        return (bool) $this->collection->count($this->convertFilter($filter));
    }
}
