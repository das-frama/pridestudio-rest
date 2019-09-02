<?php

declare(strict_types=1);

namespace app\domain\system;

use app\domain\setting\SettingRepositoryInterface;
use app\entity\Setting;
use app\storage\mongodb\SettingRepository;

/**
 * SystemService class
 */
class SystemService
{
    /** @var SettingRepositoryInterface */
    private $settingsRepo;

    public function __construct(SettingRepository $settingsRepo)
    {
        $this->settingsRepo = $settingsRepo;
    }

    public function initSettings(): bool
    {
        // Prepare settings.
        $array = require(APP_DIR . '/data/init/settings.php');
        $settings = [];
        foreach ($array as $item) {
            $setting = new Setting;
            $setting->key = $item['key'];
            $setting->value = $item['value'];
            $setting->is_active = true;
            $settings[] = $setting;
        }
        // Check if settings already exists.
        $inserted = $this->settingsRepo->insertManyIfNotExists($settings);

        return $inserted > 0;
    }
}
