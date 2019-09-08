<?php

declare(strict_types=1);

namespace app\http\exception;

use app\http\exception\base\HttpException;

class ResourceNotFoundException extends HttpException
{
    protected $code = 404;
    protected $message = "Resource not found.";
}
