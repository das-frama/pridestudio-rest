<?php
declare(strict_types=1);

namespace App\Http\Requests\Login;

use App\Http\Requests\Base\AbstractRequest;

/**
 * Class LoginRequest
 * @package App\Http\Requests\Login
 */
class LoginRequest extends AbstractRequest
{
    public string $email;
    public string $password;

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string:6'],
        ];
    }
}