<?php

declare(strict_types=1);

namespace app\domain\system;

use app\entity\Setting;
use app\domain\setting\SettingRepositoryInterface;

/**
 * SystemService class
 */
class SystemService
{
    /** @var SettingRepositoryInterface */
    private $settingsRepo;

    public function __construct(SettingRepositoryInterface $settingsRepo)
    {
        $this->settingsRepo = $settingsRepo;
    }

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
