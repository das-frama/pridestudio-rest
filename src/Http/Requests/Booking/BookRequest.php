<?php
declare(strict_types=1);

namespace App\Http\Requests\Booking;

use App\Http\Requests\Base\AbstractRequest;

class BookRequest extends AbstractRequest
{
    public string $hall_id;
    public array $reservations;
    public array $service_ids;
    public string $comment;
    public array $client;
    public string $coupon;

    /**
     * @return array|\string[][]
     */
    public function rules(): array
    {
        return [
            'hall_id' => ['required', 'objectId', 'exists:halls'],
            'reservations' => ['required', 'array'],
            'reservations.$.start_at' => ['int'],
            'reservations.$.length' => ['int:0:1440'],
            'service_ids' => ['array', 'exists:services'],
            'comment' => ['string'],
            'client' => ['required'],
            'client.name' => ['string:3:255'],
            'client.email' => ['email'],
            'client.phone' => ['string'],
            'coupon' => ['string'],
        ];
    }
}
