<?php

namespace App\Http\Resources\Base;

use JsonSerializable;

abstract class AbstractResource implements JsonSerializable
{
    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [];
    }
}
