<?php

declare(strict_types=1);

namespace app\http\exception;

use RuntimeException;

class ArgumentMismatchException extends RuntimeException
{
    public $code = 422;
}
