<?php

declare(strict_types=1);

namespace app\domain\calendar;

use app\entity\Reservation;

/**
 * CalendarDocument class.
 */
class CalendarDocument
{
    /** @var int */
    public $year;

    /** @var int */
    public $week;

    /** @var array */
    public $dates = [];

    /** @var Reservation[] */
    public $reservations = [];

    /** @var array   */
    public $limitations = [];
}
