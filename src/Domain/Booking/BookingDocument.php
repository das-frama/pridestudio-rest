<?php

declare(strict_types=1);

namespace App\Domain\Booking;

use App\Domain\Calendar\CalendarDocument;
use App\Entity\Hall;
use App\Entity\Service;

class BookingDocument
{
    public array $settings;
    public Hall $hall;
    /** @var Service[] */
    public array $services;
    public CalendarDocument $calendar;
}
