<?php

declare(strict_types=1);

namespace App\Domain\Setting;

use App\Entity\Setting;

class SettingService
{
    private SettingRepositoryInterface $settingRepo;

    public function __construct(SettingRepositoryInterface $repo)
    {
        $this->settingRepo = $repo;
    }

    /**
     * Get all settings.
     * @param array $params
     * @param array $include
     * @return Setting[]
     */
    public function findAll(array $params, array $include = []): array
    {
        $page = intval($params['page'] ?? 0);
        $limit = intval($params['limit'] ?? 0);
        // Sort.
        $sort = [];
        if (isset($params['orderBy'])) {
            $sort[$params['orderBy']] = $params['ascending'] == 0 ? -1 : 1;
        } else {
            $sort['key'] = 1;
        }
        // Skip.
        $skip = 0;
        if ($page > 0) {
            $skip = $limit * ($page - 1);
        }
        // Query.
        $filter = [];
        if (isset($params['query'])) {
            $filter = array_fill_keys(['key', 'value'], $params['query']);
            return $this->settingRepo->search($filter, $limit, $skip, $sort, $include);
        }
        return $this->settingRepo->findAll($filter, $limit, $skip, $sort, $include);
    }

    /**
     * Get setting by group name.
     * @param string $name
     * @return Setting[]
     */
    public function findByGroup(string $name, array $include = []): array
    {
        return $this->settingRepo->findByRegEx("^{$name}\_", true, $include);
    }

    /**
     * Get setting by key.
     * @param string $key
     * @param array $include
     * @return Setting|null
     */
    public function findByKey(string $key, array $include = []): ?Setting
    {
        return $this->settingRepo->findOne(['key' => $key], $include);
    }

    /**
     * Count settings.
     * @return int
     */
    public function count()
    {
        return $this->settingRepo->count();
    }
}
