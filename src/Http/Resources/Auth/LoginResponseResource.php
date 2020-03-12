<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\Base\AbstractResource;

class LoginResponseResource extends AbstractResource
{
    public string $access_token;
    public string $refresh_token;
    public int $expires_in;
}
