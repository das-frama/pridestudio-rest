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

    public function __construct()
    {
        settype($this->dates, 'array');
    }
}
