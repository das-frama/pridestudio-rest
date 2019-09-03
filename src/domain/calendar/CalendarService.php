<?php

declare(strict_types=1);

namespace app\domain\calendar;

use app\domain\record\RecordRepositoryInterface;
use app\domain\setting\SettingRepositoryInterface;
use app\entity\Reservation;
use app\storage\mongodb\RecordRepository;
use app\storage\mongodb\SettingRepository;
use MongoDB\BSON\ObjectId;
use DateTime;

/**
 * Class CalendarService
 * @package app\domain\calendar
 */
class CalendarService
{
    /** @var SettingRepositoryInterface */
    private $settingsRepo;

    /** @var RecordRepositoryInterface */
    private $recordsRepo;

    public function __construct(RecordRepository $recordsRepo, SettingRepository $settingsRepo)
    {
        $this->recordsRepo = $recordsRepo;
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
     * @return CalendarDocument
     */
    public function weekdays(string $hallID, int $year = null, int $week = null): CalendarDocument
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
                return new CalendarDocument;
            }
            $dates[] = date('Y-m-d', $time);
        }

        $firstDate = new DateTime($dates[0]);
        $lastDate = new DateTime($dates[6]);
        $document = new CalendarDocument;
        $document->year = (int) $lastDate->format('Y');
        $document->week = (int) $lastDate->format('W');
        $document->dates = $dates;
        $document->reservations = $this->findReservations(
            $hallID,
            $firstDate->getTimestamp(),
            $lastDate->getTimestamp()
        );

        return $document;
    }

    /**
     * Find reservations in hall between startAt and endAt.
     * @param string $startAt
     * @param int $startAt
     * @param int $endAt
     * @return Reservation[]
     */
    private function findReservations(string $hallID, int $startAt, int $endAt): array
    {
        $filter = [
            'hall_id' => new ObjectId($hallID),
            'reservations.start_at' => ['$gte' => $startAt, '$lt' => $endAt]
        ];
        return $this->recordsRepo->findReservations($filter);
    }
}
