<?php
declare(strict_types=1);

namespace App\Http\Resources\Base;

use JsonSerializable;

abstract class AbstractResource implements JsonSerializable
{
    /**
     * AbstractResource constructor.
     * @param array $props
     */
    public function __construct(array $props = [])
    {
        foreach ($props as $prop => $value) {
            if (property_exists($this, $prop)) {
                $this->{$prop} = $value;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $reflectionProperties = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
        $properties = [];
        foreach ($reflectionProperties as $reflectionProperty) {
            $name = $reflectionProperty->getName();
            if ($reflectionProperty->isInitialized($this)) {
                $properties[$name] = $reflectionProperty->getValue($this);
            }
        }
        return $properties;
    }
}
