<?php

declare(strict_types=1);

namespace app\domain\calendar;

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
    public $dates;

    /** @var array */
    public $reservations;

    public function __construct()
    {
        settype($this->dates, 'array');
        settype($this->reservations, 'array');
    }
}