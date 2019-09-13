<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use app\entity\Setting;
use app\domain\setting\SettingRepositoryInterface;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\BSON\Regex;

/**
 * Class SettingRepository
 * @package app\storage\mongodb
 */
class SettingRepository implements SettingRepositoryInterface
{
    use RepositoryTrait;

    /** @var Collection */
    private $collection;

    /** @var array */
    private $defaultOptions;

    /**
     * SettingRepository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->collection = $client->selectDatabase('pridestudio')->selectCollection('settings');
        $this->defaultOptions = [
            'typeMap' => [
                'root' => Setting::class,
                'document' => 'array',
            ],
        ];
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
        return $this->internalFindAll($filter, $this->defaultOptions, $include);
    }

    /**
     * {@inheritDoc}
     */
    public function findOne(array $filter, array $include = []): ?Setting
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

    /**
     * Save settings.
     * @return bool
     */
    public function save(): bool
    {
        return false;
    }
}
