<?php

declare(strict_types=1);

namespace App\Storage\MongoDB;

use App\Entity\Coupon;
use App\Domain\Record\CouponRepositoryInterface;
use App\Storage\MongoDB\Base\AbstractRepository;
use MongoDB\Client;

/**
 * Class CouponRepository
 * @package App\Storage\MongoDB
 */
class CouponRepository extends AbstractRepository implements CouponRepositoryInterface
{
    /**
     * CouponRepository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct(getenv('DB_DATABASE'), 'coupons', $client);
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
            'updated_at' => ['bsonType' => 'int'],
            'created_by' => ['bsonType' => 'objectId'],
            'updated_by' => ['bsonType' => 'objectId'],
        ], ['code', 'factor', 'is_active']);
    }
}
