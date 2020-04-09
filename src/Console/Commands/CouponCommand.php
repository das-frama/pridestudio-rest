<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\App;
use App\Console\Commands\Base\AbstractCommand;
use App\Entities\Coupon;
use App\Repositories\CouponRepositoryInterface;

/**
 * CouponCommand class.
 */
class CouponCommand extends AbstractCommand
{
    protected CouponRepositoryInterface $repo;

    /**
     * @param CouponRepositoryInterface $repo
     */
    public function __construct(CouponRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * List all coupons.
     * @return int
     */
    public function list(): int
    {
        $coupons = $this->repo->findAll();
        foreach ($coupons as $coupon) {
            /** @var Coupon $coupon */
            $discount = $coupon->factor * 100;
            $this->line("{$coupon->code}\t{$discount}%");
        }
        return 0;
    }

    /**
     * Create new coupon.
     * @param string $code
     * @param float $factor
     * @return int
     */
    public function create(string $code, float $factor): int
    {
        $coupon = new Coupon([
            'code' => $code,
            'factor' => $factor,
        ]);
        if ($this->repo->isExists(['code' => $code])) {
            $this->line("Coupon $code already exists.", App::COLOR_YELLOW);
            return 1;
        }
        $coupon = $this->repo->insert($coupon);
        if ($coupon === null) {
            $this->line("Can not create any coupon");
            return 1;
        }
        
        $this->line("Coupon $code successfully created.", App::COLOR_GREEN);
        return 0;
    }
}
