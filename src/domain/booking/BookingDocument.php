<?php

declare(strict_types=1);

namespace app\domain\booking;

use app\domain\calendar\CalendarDocument;
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

    /** @var CalendarDocument */
    public $calendar;
}
