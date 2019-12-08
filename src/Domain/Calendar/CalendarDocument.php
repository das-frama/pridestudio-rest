<?php

declare(strict_types=1);

namespace App\Domain\Calendar;

use App\Entity\Reservation;

/**
 * CalendarDocument class.
 */
class CalendarDocument
{
    public int $year;
    public int $week;
    public array $dates = [];
    /** @var Reservation[] */
    public array $reservations = [];
    public array $limitations = [];
}
