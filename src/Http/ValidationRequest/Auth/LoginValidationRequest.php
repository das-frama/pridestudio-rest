<?php
declare(strict_types=1);

namespace App\Http\ValidationRequest\Auth;

use App\Http\ValidationRequest\Base\ValidationRequestInterface;

/**
 * Class LoginValidationRequest
 * @package App\Http\Request\ValidationRequest\Auth
 */
class LoginValidationRequest implements ValidationRequestInterface
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}