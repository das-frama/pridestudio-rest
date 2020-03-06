<?php

declare(strict_types=1);

namespace App\Entity;

use App\Storage\MongoDB\Base\AbstractEntity;

/**
 * Hall class.
 */
class Hall extends AbstractEntity
{
    public string $id;
    public string $name;
    public string $slug;
    public string $description = '';
    public int $base_price;
    public string $preview_image;
    /** @var HallService[] */
    public array $services;
    /** @var PriceRule[] */
    public array $prices;
    public int $sort;
    public bool $is_active;
    public int $created_at;
    public int $updated_at;
    public ?string $created_by;
    public ?string $updated_by;

    /**
     * {@inheritDoc}
     */
    public function load(array $data, array $safe = []): void
    {
        parent::load($data, $safe);
        if (isset($data['services']) && is_array($data['services']) && in_array('services', $safe)) {
            $this->services = [];
            foreach ($data['services'] as $service) {
                $hallService = new HallService;
                $hallService->load($service);
                $this->services[] = $hallService;
            }
        }
        if (isset($data['prices']) && is_array($data['prices']) && in_array('prices', $safe)) {
            $this->prices = [];
            foreach ($data['prices'] as $price) {
                $priceRule = new PriceRule;
                $priceRule->load($price);
                $this->prices[] = $priceRule;
            }
        }
    }

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
