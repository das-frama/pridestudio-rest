<?php

declare(strict_types=1);

namespace app\domain\setting;

use app\entity\Setting;

class SettingService
{
    /**
     * @var SettingRepositoryInterface
     */
    private $settingRepo;

    public function __construct(SettingRepositoryInterface $repo)
    {
        $this->settingRepo = $repo;
    }

    /**
     * Get all settings.
     * @param array $include
     * @return Setting[]
     */
    public function findAll(array $include = []): array
    {
        return $this->settingRepo->findAll([], $include);
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
}
