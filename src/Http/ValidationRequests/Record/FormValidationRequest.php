<?php
declare(strict_types=1);

namespace App\Http\ValidationRequests\Record;

use App\Http\ValidationRequests\Base\ValidationRequestInterface;

/**
 * Class FormValidationRequest
 * @package App\Http\Request\ValidationRequest\Record
 */
class FormValidationRequest implements ValidationRequestInterface
{
    public function rules(): array
    {
        return [
            'hall_id' => ['required', 'objectId'],
            'reservations' => ['required', 'array'],
            'reservations.$.start_at' => ['required', 'int'],
            'reservations.$.length' => ['required', 'int:0:1440'],
//            'reservations.$.comment' => ['string:0:255'],
            'service_ids' => ['array'],
            'total' => ['int'],
            'status' => ['required', 'int:0:10'],
//            'comment' => ['string'],
            'client' => ['required'],
            'client.name' => ['required', 'string:3:255'],
            'client.email' => ['required', 'email'],
            'client.phone' => ['required', 'string'],
        ];
    }
}