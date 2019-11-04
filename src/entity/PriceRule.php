<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\base\AbstractEntity;
use MongoDB\BSON\ObjectId;
use DateTimeImmutable;

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

    /** @var string */
    public $time_from;

    /** @var string */
    public $time_to;

    /** @var int */
    public $schedule_mask;

    /** @var int */
    public $type;

    /** @var int */
    public $from_length;

    /** @var string */
    public $comparison;

    /** @var int */
    public $price;

    /** @var string[] */
    public $service_ids = [];

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
     */
    public static function getWeekday(int $time): int
    {
        $day = (int) (new DateTimeImmutable('@' . $time))->format('N');
        return 1 << ($day - 1);
    }
}
