<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Base\AbstractEntity;
use MongoDB\BSON\ObjectId;

class HallService extends AbstractEntity
{
    public string $category_id;
    public array $children = [];
    public array $parents = [];

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
