<?php

declare(strict_types=1);

namespace app\domain\record;

use app\entity\Coupon;

interface CouponRepositoryInterface
{
    /**
     * Find Coupon by id.
     * @param array $filter
     * @param array $include
     * @return Coupon|null
     */
    public function findOne(array $filter, array $include = []): ?Coupon;

    /**
     * Find all Coupons by filter.
     * @param array $filter
     * @param array $include
     * @return Coupon[]
     */
    public function findAll(array $filter = [], array $include = []): array;

    /**
     * Init schema validation.
     * @return bool
     */
    public function init(): bool;

    /**
     * Insert a coupon.
     * @param Coupon $coupon
     * return string|null
     */
    public function insert(Coupon $coupon): ?string;
}
