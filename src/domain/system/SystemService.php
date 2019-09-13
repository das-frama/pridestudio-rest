<?php

declare(strict_types=1);

namespace app\domain\system;

use app\domain\setting\SettingRepositoryInterface;
use app\entity\Setting;

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

    public function initSettings(): bool
    {
        // Prepare settings.
        $settings = array_map(function ($item) {
            $setting = new Setting;
            $setting->key = $item['key'];
            $setting->value = $item['value'];
            $setting->is_active = true;
            return $setting;
        }, require(APP_DIR . '/data/init/settings.php'));
        // Check if settings already exists.
        $inserted = $this->settingsRepo->insertManyIfNotExists($settings);
        return $inserted > 0;
    }
}
