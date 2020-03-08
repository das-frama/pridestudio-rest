<?php
declare(strict_types=1);

namespace App\Http\Resources\Booking;

use App\Entities\Hall;
use App\Entities\Service;
use App\Http\Resources\Base\AbstractResource;
use App\Http\Resources\CalendarResource;

class BookingResource extends AbstractResource
{
    public array $settings;
    public Hall $hall;
    /** @var Service[] */
    public array $services;
    public CalendarResource $calendar;
}
