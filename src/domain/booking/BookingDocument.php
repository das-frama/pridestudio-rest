<?php

declare(strict_types=1);

namespace app\domain\booking;

use app\entity\Hall;
use app\entity\Service;

class BookingDocument
{
    /** @var array  */
    public $settings;

    /** @var Hall */
    public $hall;

    /** @var Service[] */
    public $services;
}
