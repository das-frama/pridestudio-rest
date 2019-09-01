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

    public function save(): bool
    {
        return false;
    }
}
