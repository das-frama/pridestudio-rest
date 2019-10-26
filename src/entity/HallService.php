<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\base\AbstractEntity;
use MongoDB\BSON\ObjectId;

class HallService extends AbstractEntity
{
    /** @var string */
    public $category_id;

    /** @var array */
    public $children = [];

    /** @var array */
    public $parents = [];

    public function bsonSerialize(): array
    {
        $bson = [];
        $bson['category_id'] = new ObjectId($this->category_id);
        $bson['children'] = array_map(function (string $id) {
            return new ObjectId($id);
        }, $this->children);
        $bson['parents'] = array_map(function (string $id) {
            return new ObjectId($id);
        }, $this->parents);
        
        return $bson;
    }
}
