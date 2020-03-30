<?php
declare(strict_types=1);

namespace App\Http\Requests\Hall;

use App\Http\Requests\Base\AbstractRequest;

class FormRequest extends AbstractRequest
{
    public string $name;
    public string $slug;
    public string $preview_image;
    public int $base_price;
    public int $sort;
    public bool $is_active;
    public array $services;
    public array $prices;

    /**
     * @return array|\string[][]
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string:1:64'],
            'slug' => ['required', 'string:1:64'],
            'preview_image' => ['string:1:255'],
            'base_price' => ['int:0:999999'],
            'sort' => ['int'],
            'is_active' => ['bool'],
            'services' => ['array:0:50'],
            'services.$.category_id' => ['object_id'],
            'services.$.children' => ['array:0:16'],
            'services.$.children.$' => ['object_id'],
            'services.$.parents' => ['array:0:16'],
            'services.$.parents.$' => ['object_id'],
            'prices' => ['array:0:50'],
            'prices.$.time_from' => ['time'],
            'prices.$.time_to' => ['time'],
            'prices.$.schedule_mask' => ['int:0:127'],
            'prices.$.type' => ['enum:1,2'],
            'prices.$.from_length' => ['int:60:1440'],
            'prices.$.comparison' => ['enum:>,>=,<,<=,=,!='],
            'prices.$.price' => ['int:0:9999999'],
            'prices.$.service_ids' => ['array:0:16'],
            'prices.$.service_ids.$' => ['object_id'],
        ];
    }
}
