<?php

declare(strict_types=1);

namespace app\domain\booking;

class PaymentDocument
{
    /** @var int */
    public $price = 0;

    /** @var int */
    public $prepayment = 0;
}
