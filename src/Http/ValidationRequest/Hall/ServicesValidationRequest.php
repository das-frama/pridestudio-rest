<?php
declare(strict_types=1);

namespace App\Http\ValidationRequest\Hall;

use App\Http\ValidationRequest\Base\ValidationRequestInterface;

/**
 * Class HallValidationRequest
 * @package App\Http\Request\ValidationRequest\Hall
 */
class ServicesValidationRequest implements ValidationRequestInterface
{
    public function rules(): array
    {
        return [
            '$' => ['objectId'],
        ];
    }
}