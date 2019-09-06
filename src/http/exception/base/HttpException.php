<?php

declare(strict_types=1);

namespace app\http\exception\base;

use RuntimeException;

class HttpException extends RuntimeException
{
    /**
     * {@inheritDoc}
     */
    public function __construct(string $message = "")
    {
        if ($message !== "") {
            $this->message = $message;
        }
        parent::__construct($this->message, $this->code);
    }
}
