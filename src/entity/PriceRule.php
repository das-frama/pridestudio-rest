<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\base\AbstractEntity;
use MongoDB\BSON\ObjectId;

class PriceRule extends AbstractEntity
{
    const TYPE_PER_HOUR = 1;
    const TYPE_FIXED = 2;

    /** @var string */
    public $time_from;

    /** @var string */
    public $time_to;

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

    public function bsonSerialize(): array
    {
        $bson = parent::bsonSerialize();
        $bson['service_ids'] = array_map(function (string $id) {
            return new ObjectId($id);
        }, $this->service_ids);
        
        return $bson;
    }
}
