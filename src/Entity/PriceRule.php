<?php

declare(strict_types=1);

namespace App\Entity;

use App\Storage\MongoDB\Base\AbstractEntity;
use DateTimeImmutable;
use Exception;
use MongoDB\BSON\ObjectId;

class PriceRule extends AbstractEntity
{
    const DAY_MONDAY = 0x01;
    const DAY_TUESDAY = 0x02;
    const DAY_WEDNESDAY = 0x04;
    const DAY_THURSDAY = 0x08;
    const DAY_FRIDAY = 0x10;
    const DAY_SATURDAY = 0x20;
    const DAY_SUNDAY = 0x40;
    const DAY_WEEKDAYS = self::DAY_MONDAY | self::DAY_TUESDAY | self::DAY_WEDNESDAY | self::DAY_THURSDAY | self::DAY_FRIDAY;
    const DAY_WEEKEND = self::DAY_SATURDAY | self::DAY_SUNDAY;
    const DAY_ALL = self::DAY_WEEKDAYS | self::DAY_WEEKEND;

    const TYPE_PER_HOUR = 1;
    const TYPE_FIXED = 2;

    public ?string $time_from = null;
    public ?string $time_to = null;
    public int $schedule_mask;
    public int $type;
    public int $from_length;
    public string $comparison;
    public int $price;
    public array $service_ids = [];

    /**
     * {@inheritDoc}
     */
    public function bsonSerialize(): array
    {
        $bson = parent::bsonSerialize();
        $bson['service_ids'] = array_map(function (string $id) {
            return new ObjectId($id);
        }, $this->service_ids);

        return $bson;
    }

    /**
     * Get day of the week.
     * @param int $time
     * @return int
     * @throws Exception
     */
    public static function getWeekday(int $time): int
    {
        $day = (int)(new DateTimeImmutable('@' . $time))->format('N');
        return 1 << ($day - 1);
    }
}
