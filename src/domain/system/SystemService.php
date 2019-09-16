<?php

declare(strict_types=1);

namespace app\domain\system;

use app\domain\hall\HallRepositoryInterface;
use app\entity\Setting;
use app\domain\setting\SettingRepositoryInterface;

/**
 * SystemService class
 */
class SystemService
{
    /** @var HallRepositoryInterface */
    private $hallRepo;

    /** @var SettingRepositoryInterface */
    private $settingsRepo;

    public function __construct(
        HallRepositoryInterface $hallRepo,
        SettingRepositoryInterface $settingsRepo
    ) {
        $this->hallRepo = $hallRepo;
        $this->settingsRepo = $settingsRepo;
    }

    /**
     * Init halls collection.
     * @return bool
     */
    public function initHalls(): bool
    {
        return $this->hallRepo->init();
    }

    /**
     * Init settings collection.
     * @param array $data
     * @return bool
     */
    public function initSettings(array $data): bool
    {
        // Prepare settings.
        $settings = array_map(function ($item) {
            $setting = new Setting;
            $setting->key = $item['key'];
            $setting->value = $item['value'];
            $setting->is_active = true;
            return $setting;
        }, $data);
        // Check if settings already exists.
        $inserted = $this->settingsRepo->insertManyIfNotExists($settings);
        return $inserted > 0;
    }
}
