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
     * @return Setting[]
     */
    public function findAll(): array
    {
        return $this->settingRepo->findAll(true);
    }

    /**
     * @return Setting[]
     */
    public function findByGroup(string $name): array
    {
        return $this->settingRepo->findByRegEx("^{$name}\_", true);
    }

    /**
     * @return Hall|null
     */
    public function findByKey(string $key): ?Setting
    {
        return $this->settingRepo->findByKey($key, true);
    }
}
