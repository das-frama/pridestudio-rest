<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\Coupon;
use App\Entities\Hall;
use App\Entities\Service;
use App\Entities\Setting;
use App\Repositories\ClientRepositoryInterface;
use App\Repositories\CouponRepositoryInterface;
use App\Repositories\HallRepositoryInterface;
use App\Repositories\RecordRepositoryInterface;
use App\Repositories\SettingRepositoryInterface;

/**
 * Class BookingService
 * @package App\Services
 */
class BookingService
{
    protected RecordRepositoryInterface $recordRepo;
    protected CouponRepositoryInterface $couponRepo;
    protected ClientRepositoryInterface $clientRepo;
    protected HallRepositoryInterface $hallRepo;
    protected SettingRepositoryInterface $settingRepo;

    /**
     * BookingService constructor.
     * @param RecordRepositoryInterface $recordRepo
     * @param CouponRepositoryInterface $couponRepo
     * @param ClientRepositoryInterface $clientRepo
     * @param HallRepositoryInterface $hallRepo
     * @param SettingRepositoryInterface $settingRepo
     */
    public function __construct(
        RecordRepositoryInterface $recordRepo,
        CouponRepositoryInterface $couponRepo,
        ClientRepositoryInterface $clientRepo,
        HallRepositoryInterface $hallRepo,
        SettingRepositoryInterface $settingRepo
    ) {
        $this->recordRepo = $recordRepo;
        $this->couponRepo = $couponRepo;
        $this->clientRepo = $clientRepo;
        $this->hallRepo = $hallRepo;
        $this->settingRepo = $settingRepo;
    }

    /**
     * @return Hall|null
     */
    public function defaultHall(): ?Hall
    {
        // Find out default hall.
        $setting = $this->settingRepo->findOne(['key' => 'calendar_default_hall']);
        if (!$setting instanceof Setting) {
            return null;
        }
        // Find hall.
        $hall = $this->hallRepo->findOne(['slug' => $setting->value]);
        return $hall instanceof Hall ? $hall : null;
    }

    /**
     * @param string $slug
     * @return Hall|null
     */
    public function hall(string $slug): ?Hall
    {
        $hall = $this->hallRepo->findOne(['slug' => $slug]);
        return $hall instanceof Hall ? $hall : null;
    }

    /**
     * Find calendar settings
     * @param string $prefix
     * @return array
     */
    public function settings(string $prefix): array
    {
        // Find out default hall.
        $settings = $this->settingRepo->findByRegEx("^{$prefix}\_", true);
        return array_column($settings, 'value', 'key');
    }

    /**
     * @param string $hallID
     * @return Service[]
     */
    public function services(string $hallID): array
    {
        return $this->hallRepo->findServices(['id' => $hallID], []);
    }

    /**
     * Find coupon by code.
     * @param string $code
     * @return Coupon|null
     */
    public function findCoupon(string $code): ?Coupon
    {
        $coupon = $this->couponRepo->findOne(['code' => $code]);
        return $coupon instanceof Coupon ? $coupon : null;
    }
}
