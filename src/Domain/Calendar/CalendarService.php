<?php

declare(strict_types=1);

namespace App\Domain\Calendar;

use App\Domain\Record\RecordRepositoryInterface;
use App\Domain\Setting\SettingRepositoryInterface;
use App\Entity\Reservation;
use MongoDB\BSON\ObjectId;
use DateTime;
use DateTimeImmutable;

/**
 * Class CalendarService
 * @package App\Domain\calendar
 */
class CalendarService
{
    private SettingRepositoryInterface $settingsRepo;
    private RecordRepositoryInterface $recordsRepo;

    public function __construct(
        RecordRepositoryInterface $recordsRepo,
        SettingRepositoryInterface $settingsRepo
    ) {
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

        $firstDate = new DateTimeImmutable($dates[0]);
        $lastDate = (new DateTimeImmutable($dates[6]))->setTime(23, 59, 59);
        $document = new CalendarDocument;
        $document->year = (int) $lastDate->format('Y');
        $document->week = (int) $lastDate->format('W');
        $document->dates = $dates;
        $document->reservations = $this->findReservations(
            $hallID,
            $firstDate->getTimestamp(),
            $lastDate->getTimestamp()
        );
        $document->limitations = $this->findLimitations($dates);
        return $document;
    }

    /**
     * Find limitations for passed dates.
     * @param array $dates
     * @return array
     */
    private function findLimitations(array $dates): array
    {
        $setting = $this->settingsRepo->findOne(['key' => 'calendar_max_booking_range']);
        $deltaMonth = $setting === null ? 1 : (int) $setting->value;

        $minutes = date('i');
        $nextHourDate = (new DateTimeImmutable())
            ->modify('+1 hour')
            ->modify('-' . $minutes . 'minutes');
        $currentDate = (new DateTimeImmutable())
            ->setTime(0, 0, 0, 0);
        $futureDate = new DateTimeImmutable("last day of +{$deltaMonth} month");

        // Calculate limitations.
        $limitations = array_map(function (string $dateStr) use ($currentDate, $futureDate, $nextHourDate) {
            $date = new DateTimeImmutable($dateStr);
            if ($date == $currentDate) {
                $diff = $nextHourDate->diff($date);
                $length = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                return [
                    'start_at' => $date->getTimestamp(),
                    'length' => $length
                ];
            } elseif ($date < $currentDate || $date >= $futureDate) {
                return [
                    'start_at' => $date->getTimestamp(),
                    'length' => 24 * 60 // 24 hours
                ];
            }
            return [];
        }, $dates);

        return array_values(array_filter($limitations, function (array $limitation) {
            return count($limitation) > 0;
        }));
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
            'reservations.start_at' => ['$gte' => $startAt, '$lt' => $endAt],
        ];
        return $this->recordsRepo->findReservations($filter);
    }
}
