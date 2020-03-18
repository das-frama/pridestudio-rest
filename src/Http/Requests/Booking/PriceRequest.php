<?php
declare(strict_types=1);

namespace App\Http\Requests\Booking;

use App\Http\Requests\Base\AbstractRequest;

class PriceRequest extends AbstractRequest
{
    public string $hall_id;
    public array $reservations;
    public array $service_ids;
    public string $coupon;

    /**
     * @return array|\string[][]
     */
    public function rules(): array
    {
        return [
            'hall_id' => ['required', 'objectId'],
            'reservations' => ['required', 'array'],
            'reservations.$.start_at' => ['required', 'int'],
            'reservations.$.length' => ['required', 'int:0:1440'],
            'service_ids' => ['array'],
            'coupon' => ['string'],
        ];
    }
}
