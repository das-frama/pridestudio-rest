<?php
declare(strict_types=1);

namespace App\Http\Resources\Booking;

use App\Http\Resources\Base\AbstractResource;

class PaymentResource extends AbstractResource
{
    public int $price = 0;
    public int $prepayment = 0;
}
