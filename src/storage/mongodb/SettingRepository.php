<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Setting;
use app\domain\setting\SettingRepositoryInterface;
use app\storage\mongodb\base\AbstractRepository;
use MongoDB\Client;
use MongoDB\BSON\Regex;

/**
 * Class SettingRepository
 * @package app\storage\mongodb
 */
class SettingRepository extends AbstractRepository implements SettingRepositoryInterface
{
    /**
     * SettingRepository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct(getenv('DB_DATABASE'), 'settings', $client);
        $this->defaultOptions = [
            'typeMap' => [
                'root' => Setting::class,
                'document' => 'array',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function init(): bool
    {
        // Create schema validation.
        return $this->createSchemaValidation('settings', [
            'key' => ['bsonType' => 'string'],
            'is_active' => ['bsonType' => 'bool'],
        ], ['key', 'value']);
    }

    /**
     * Find a setting by regular expression.
     * @param string $regex
     * @param bool $onlyActive
     * @param array $include
     * @return Setting[]
     */
    public function findByRegEx(string $regex, bool $onlyActive, array $include = []): array
    {
        $filter = ['key' => new Regex($regex, 'i')];
        if ($onlyActive) {
            $filter['is_active'] = true;
        }
        return $this->findAll($filter, 0, 0, [], $include);
    }

    /**
     * @param Setting[] $data
     * @return int
     */
    public function insertManyIfNotExists(array $settings): int
    {
        $upsertedCount = 0;
        foreach ($settings as $setting) {
            if ($setting instanceof Setting) {
                $result = $this->collection->updateOne(
                    ['key' => $setting->key],
                    ['$set' => [
                        'key' => $setting->key,
                        'value' => $setting->value,
                        'is_active' => true,
                    ]],
                    ['upsert' => true]
                );
                $upsertedCount += $result->getUpsertedCount();
            }
        }

        return $upsertedCount;
    }
}
