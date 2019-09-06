<?php

declare(strict_types=1);

namespace app\http\exception;

use app\http\exception\base\HttpException;

class UprocessableEntityException extends HttpException
{
    protected $code = 422;
    protected $message = "Unprocessable entity.";
}
