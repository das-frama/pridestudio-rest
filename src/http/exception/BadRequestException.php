<?php

declare(strict_types=1);

namespace app\http\exception;

use app\http\exception\base\HttpException;

class BadRequestException extends HttpException
{
    protected $code = 400;
    protected $message = "Bad request.";
}
