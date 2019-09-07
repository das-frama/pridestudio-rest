<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\Entity;

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
    public $services_object = [];

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
    public function bsonUnserialize(array $data): void
    {
        parent::bsonUnserialize($data);

        if ($this->preview_image !== null) {
            $this->preview_image = HOST . $this->preview_image;
        }
        if ($this->detail_image !== null) {
            $this->detail_image = HOST . $this->detail_image;
        }
    }
}
