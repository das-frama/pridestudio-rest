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

    /** @var float */
    public $base_price;

    /** @var string */
    public $preview_image;

    /** @var string */
    public $detail_image;

    /** @var int */
    public $sort;

    /** @var bool */
    public $is_active;

    /** @var int */
    public $created_at;

    /** @var int */
    public $updated_at;

    /** @var int */
    public $created_by;

    /** @var int */
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
