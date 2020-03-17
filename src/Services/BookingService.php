<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\Coupon;
use App\Repositories\CouponRepositoryInterface;

/**
 * Class BookingService
 * @package App\Services
 */
class BookingService
{
    protected CouponRepositoryInterface $couponRepo;

    /**
     * BookingService constructor.
     * @param CouponRepositoryInterface $couponRepo
     */
    public function __construct(CouponRepositoryInterface $couponRepo)
    {
        $this->couponRepo = $couponRepo;
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
