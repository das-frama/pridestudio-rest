<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Entities\Reservation;

/**
 * CalendarDocument class.
 */
class CalendarResource
{
    public int $year;
    public int $week;
    public array $dates = [];
    /** @var Reservation[] */
    public array $reservations = [];
    public array $limitations = [];
}
