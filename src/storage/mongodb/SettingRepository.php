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
    /** @var Collection */
    private $collection;

    /** @var array */
    private $options;

    /**
     * SettingRepository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->collection = $client->selectDatabase('pridestudio')->selectCollection('settings');
        $this->options = [
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
     * @return Setting[]
     */
    public function findByRegEx(string $regex, bool $onlyActive): array
    {
        $filter = ['key' => new Regex($regex, 'i')];
        if ($onlyActive) {
            $filter['is_active'] = true;
        }

        $result = [];
        $cursor = $this->collection->find($filter, $this->options);
        foreach ($cursor as $setting) {
            if ($setting instanceof Setting) {
                $result[] = $setting;
            }
        }

        return $result;
    }

    /**
     * Find a setting by key.
     * @param string $key
     * @param bool $onlyActive
     * @return Setting|null
     */
    public function findByKey(string $key, bool $onlyActive): ?Setting
    {
        $filter = ['key' => $key];
        if ($onlyActive) {
            $filter['is_active'] = true;
        }

        $setting = $this->collection->findOne($filter, $this->options);
        if ($setting instanceof Setting) {
            return $setting;
        }

        return null;
    }

    /**
     * Find all settings.
     * @param bool $onlyActive
     * @return Setting[]
     */
    public function findAll(bool $onlyActive): array
    {
        $filter = [];
        if ($onlyActive) {
            $filter['is_active'] = true;
        }

        $result = [];
        $cursor = $this->collection->find($filter, $this->options);
        foreach ($cursor as $setting) {
            if ($setting instanceof Setting) {
                $result[] = $setting;
            }
        }

        return $result;
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
