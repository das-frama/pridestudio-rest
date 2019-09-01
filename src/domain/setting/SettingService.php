<?php

declare(strict_types=1);

namespace app\domain\setting;

use app\entity\Setting;
use app\storage\mongodb\SettingRepository;

class SettingService
{
    /**
     * @var SettingRepositoryInterface
     */
    private $settingRepo;

    public function __construct(SettingRepository $repo)
    {
        $this->settingRepo = $repo;
    }

    /**
     * Get all settings.
     * @return Setting[]
     */
    public function findAll(): array
    {
        return $this->settingRepo->findAll(true);
    }

    /**
     * Get setting by group name.
     * @param string $name
     * @return Setting[]
     */
    public function findByGroup(string $name): array
    {
        return $this->settingRepo->findByRegEx("^{$name}\_", true);
    }

    /**
     * Get setting by key.
     * @param string $key
     * @return Setting|null
     */
    public function findByKey(string $key): ?Setting
    {
        return $this->settingRepo->findByKey($key, true);
    }
}
