<?php

namespace app\http\exception;

use app\http\exception\base\HttpException;

class MethodNotAllowedException extends HttpException
{
    protected $code = 405;
    protected $message = "Method not allowed.";
}
