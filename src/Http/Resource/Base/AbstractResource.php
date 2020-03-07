<?php

namespace App\Http\Resource\Base;

use JsonSerializable;

abstract class AbstractResource implements JsonSerializable, ResourceInterface
{
    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [];
    }

    abstract public function rules(): array;
}