<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\Coupon;
use App\Repositories\ClientRepositoryInterface;
use App\Repositories\CouponRepositoryInterface;
use App\Repositories\HallRepositoryInterface;
use App\Repositories\RecordRepositoryInterface;

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

    /**
     * BookingService constructor.
     * @param RecordRepositoryInterface $recordRepo
     * @param CouponRepositoryInterface $couponRepo
     * @param ClientRepositoryInterface $clientRepo
     * @param HallRepositoryInterface $hallRepo
     */
    public function __construct(
        RecordRepositoryInterface $recordRepo,
        CouponRepositoryInterface $couponRepo,
        ClientRepositoryInterface $clientRepo,
        HallRepositoryInterface $hallRepo
    ) {
        $this->recordRepo = $recordRepo;
        $this->couponRepo = $couponRepo;
        $this->clientRepo = $clientRepo;
        $this->hallRepo = $hallRepo;
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
