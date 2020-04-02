<?php
declare(strict_types=1);

namespace App\Repositories\MongoDB;

use App\Entities\StaticText;
use App\Repositories\Base\AbstractRepository;
use App\Repositories\StaticRepositoryInterface;
use MongoDB\Client as MongoDBClient;

/**
 * Class StaticRepository
 * @package App\Repositories\MongoDB
 */
class StaticRepository extends AbstractRepository implements StaticRepositoryInterface
{
    /**
     * RecordRepository constructor.
     * @param MongoDBClient $client
     */
    public function __construct(MongoDBClient $client)
    {
        parent::__construct(getenv('DB_DATABASE'), 'static', $client);
        $this->defaultOptions = [
            'typeMap' => [
                'root' => StaticText::class,
                'document' => 'array',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function init(): bool
    {
        // Create schema validation.
        return $this->createSchemaValidation('static', [
            'key' => ['bsonType' => 'string'],
            'text' => ['bsonType' => 'string'],
        ], ['key']);
    }
}
