<?php

declare(strict_types=1);

namespace app\domain\system;

use app\entity\Setting;
use app\domain\hall\HallRepositoryInterface;
use app\domain\record\CouponRepositoryInterface;
use app\domain\setting\SettingRepositoryInterface;
use app\entity\Coupon;

/**
 * SystemService class
 */
class SystemService
{
    /** @var HallRepositoryInterface */
    private $hallsRepo;

    /** @var CouponRepositoryInterface */
    private $couponsRepo;

    /** @var SettingRepositoryInterface */
    private $settingsRepo;

    public function __construct(
        HallRepositoryInterface $hallsRepo,
        CouponRepositoryInterface $couponsRepo,
        SettingRepositoryInterface $settingsRepo
    ) {
        $this->hallsRepo = $hallsRepo;
        $this->couponsRepo = $couponsRepo;
        $this->settingsRepo = $settingsRepo;
    }

    /**
     * Init halls collection.
     * @return bool
     */
    public function initHalls(): bool
    {
        return $this->hallsRepo->init();
    }

    /**
     * Init coupons collection.
     * @return bool
     */
    public function initCoupons(): bool
    {
        // Init schema stuff.
        if (!$this->couponsRepo->init()) {
            return false;
        }
        return true;
        // Insert a test coupon.
        // $coupon = new Coupon;
        // $coupon->code = "TESTDUDE";
        // $coupon->factor = 0.25;
        // $coupon->length = 3 * 24 * 60 * 60; // 3 days.
        // $coupon->is_active = true;
        // $id = $this->couponsRepo->insert($coupon);
        // return (bool) $id;
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
