<?php

namespace app\http\exception;

use RuntimeException;

class ArgumentMismatchException extends RuntimeException
{
    public $code = 422;
}
