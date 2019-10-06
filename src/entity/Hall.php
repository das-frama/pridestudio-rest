<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\Entity;
use MongoDB\BSON\ObjectId;

/**
 * Hall class.
 */
class Hall extends Entity
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $slug;

    /** @var string */
    public $description;

    /** @var int */
    public $base_price;

    /** @var string */
    public $preview_image;

    /** @var string */
    public $detail_image;

    /** @var array */
    public $services = [];

    /** @var Service[] */
    public $services_join = [];

    /** @var array */
    public $prices = [];

    /** @var int */
    public $sort;

    /** @var bool */
    public $is_active;

    /** @var int */
    public $created_at;

    /** @var int */
    public $updated_at;

    /** @var string */
    public $created_by;

    /** @var string */
    public $updated_by;

    /**
     * {@inheritDoc}
     */
    public function bsonSerialize(): array
    {
        $bson = parent::bsonSerialize();
        unset($bson['services_join']);

        // Convert services.
        $bson['services'] = array_map(function (array $service) {
            if (isset($service['category_id'])) {
                $service['category_id'] = new ObjectId($service['category_id']);
            }
            if (isset($service['children'])) {
                $service['children'] = array_map(function (string $child) {
                    return new ObjectId($child);
                }, $service['children']);
            }
            if (isset($service['parents'])) {
                $service['parents'] = array_map(function (string $parent) {
                    return new ObjectId($parent);
                }, $service['parents']);
            }
            return $service;
        }, $bson['services']);

        // Convert prices.
        $bson['prices'] = array_map(function (array $price) {
            if (isset($price['service_ids'])) {
                $price['service_ids'] = array_map(function (string $serviceID) {
                    return new ObjectId($serviceID);
                }, $price['service_ids']);
            }
            return $price;
        }, $bson['prices']);

        return $bson;
    }

    /**
     * Get default selected services.
     * @return array
     */
    public function getDefaultServices(): array
    {
        $selected = [];
        foreach ($this->services as $service) {
            if (isset($service['parents'])) {
                $common = array_intersect($selected, $service['parents']);
                if (!empty($common)) {
                    $selected[] = reset($service['children']);
                }
            } else {
                $selected[] = reset($service['children']);
            }
        }
        return $selected;
    }
}
