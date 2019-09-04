<?php

namespace app\http\exception;

use RuntimeException;

class UprocessableEntityException extends RuntimeException
{
    public $code = 422;
}
