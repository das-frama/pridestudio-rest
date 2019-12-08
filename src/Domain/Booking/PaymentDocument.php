<?php

declare(strict_types=1);

namespace App\Domain\Booking;

class PaymentDocument
{
    public int $price = 0;
    public int $prepayment = 0;
}
