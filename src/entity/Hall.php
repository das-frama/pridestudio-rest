<?php

declare(strict_types=1);

namespace app\entity;

use app\storage\mongodb\base\AbstractEntity;

/**
 * Hall class.
 */
class Hall extends AbstractEntity
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

    /** @var HallService[] */
    public $services = [];

    /** @var PriceRule[] */
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
     * Get default selected services.
     * @return array
     */
    public function getDefaultServices(): array
    {
        $selected = [];
        foreach ($this->services as $service) {
            if (!empty($service->parents)) {
                $common = array_intersect($selected, $service->parents);
                if (!empty($common)) {
                    $selected[] = reset($service->children);
                }
            } else {
                $selected[] = reset($service->children);
            }
        }
        return $selected;
    }
}
