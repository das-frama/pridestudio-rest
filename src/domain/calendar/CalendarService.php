<?php

declare(strict_types=1);

namespace app\domain\calendar;

use app\domain\setting\SettingRepositoryInterface;
use app\storage\mongodb\SettingRepository;

/**
 * Class CalendarService
 * @package app\domain\calendar
 */
class CalendarService
{
    /** @var SettingRepositoryInterface */
    private $settingsRepo;

    public function __construct(SettingRepository $settingsRepo)
    {
        $this->settingsRepo = $settingsRepo;
    }

    /**
     * Get current month from 1 to 12.
     * @return int
     */
    public function currentMonth(): int
    {
        return (int) date('n');
    }

    /**
     * Get days of the week by week number and year.
     * @param int $year default current year
     * @param int $week default current weak
     * @return array
     */
    public function weekdays(int $year = null, int $week = null): array
    {
        if ($year === null) {
            $year = (int) date('Y');
        }
        if ($week === null) {
            $week = (int) date('W');
        }
        $year = abs($year);
        $week = abs($week);

        $dates = [];
        for ($day = 1; $day <= 7; $day++) {
            $str = sprintf("%04dW%02d%d", $year, $week, $day);
            $time = strtotime($str);
            if ($time === false) {
                return [];
            }
            $dates[] = date('Y-m-d', $time);
        }
        return $dates;
    }
}
