<?php

declare(strict_types=1);

namespace app\domain\booking;

class BookingDocument
{
    /** @var int */
    public $price;

    /** @var int */
    public $prepayment;

    public function __construct()
    {
        $this->price = 0;
        $this->prepayment = 0;
    }
}
