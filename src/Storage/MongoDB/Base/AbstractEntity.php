<?php

declare(strict_types=1);

namespace App\Storage\MongoDB\Base;

use JsonSerializable;
use ReflectionObject;
use ReflectionProperty;
use ReflectionClass;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Persistable;

/**
 * Entity is a base class for mongodb records.
 */
abstract class AbstractEntity implements Persistable, JsonSerializable
{
    protected $include = [];
    protected $expand = [];
    protected $public = [];
    protected $unserialized = false;

    public function __construct()
    {
        // Set default values by it's type for each property.
        $reflectionProperties = (new ReflectionClass(static::class))->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($reflectionProperty->getValue() !== null) {
                continue;
            }
            $name = $reflectionProperty->getName();
            switch ($reflectionProperty->getType()) {
                case 'string':
                    $this->{$name} = '';
                    break;
                case 'integer':
                    $this->{$name} = 0;
                    break;
                case 'float':
                    $this->{$name} = 0.0;
                    break;
                case 'boolean':
                    $this->{$name} = false;
                    break;
                case 'array':
                    $this->{$name} = [];
                    break;
                case 'object':
                    $this->{$name} = null;
                    break;
            }
        }
    }

    /**
     * Load array of data to the Entity.
     * Every Entity should reimplement this method to load the data.
     * @param array $data
     * @param array $safe
     * @return void
     */
    public function load(array $data, array $safe = []): void
    {
        foreach ($data as $key => $value) {
            $isSafe = empty($safe) || in_array($key, $safe);
            if (property_exists($this, $key) && $isSafe) {
                $this->{$key} = $value;
            }
        }
    }

    public function setInclude(array $properties): void
    {
        $this->include = $properties;
    }

    public function setExpand(string $key, object $expand): void
    {
        $this->expand[$key] = $expand;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): array
    {
        $reflectionProperties = (new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC);
        $properties = [];

        foreach ($reflectionProperties as $reflectionProperty) {
            $name = $reflectionProperty->getName();
            $isInclude = empty($this->include) || in_array($name, $this->include);
            $isPublic = empty($this->public) || in_array($name, $this->public);
            if ($isInclude && $isPublic) {
                $properties[$name] = $reflectionProperty->getValue($this);
            }
        }
        // Display expand properties.
        foreach ($this->expand as $key => $expand) {
            $properties[$key] = $expand;
        }

        return $properties;
    }

    /**
     * {@inheritDoc}
     */
    public function bsonSerialize(): array
    {
        $properties = static::publicProperties();
        $bson = [];

        foreach ($properties as $property) {
            if ($this->{$property} === null) {
                continue;
            }
            switch ($property) {
                case 'id':
                    if (!empty($this->id)) {
                        $bson['_id'] = new ObjectId($this->id);
                    }
                    break;

                case 'created_by':
                case 'updated_by':
                    if (!empty($this->{$property})) {
                        $bson[$property] = new ObjectId($this->{$property});
                    }
                    break;

                case 'created_at':
                    continue 2;

                default:
                    $bson[$property] = $this->{$property};
            }
        }

        return $bson;
    }

    /**
     * {@inheritDoc}
     */
    public function bsonUnserialize(array $data): void
    {
        if (isset($data['_id']) && $data['_id'] instanceof ObjectId) {
            $this->id = (string) $data['_id'];
            if (property_exists($this, 'created_at')) {
                $this->created_at = $data['_id']->getTimestamp();
            }
        }
        foreach ($data as $property => $value) {
            if (!property_exists($this, (string) $property)) {
                continue;
            }
            if ($value instanceof ObjectId) {
                $this->{$property} = (string) $value;
            } elseif ($value instanceof UTCDateTime) {
                if (strpos($property, '_at', -3)) {
                    $this->{$property} = $value->toDateTime()->getTimestamp();
                } else {
                    $this->{$property} = $value->toDateTime();
                }
            } elseif (is_array($value)) {
                $this->{$property} = array_map([$this, 'convertArray'], $value);
            } else {
                $this->{$property} = $value;
            }
        }
        $this->unserialized = true;
    }

    private function convertArray($value)
    {
        if ($value instanceof ObjectId) {
            return (string) $value;
        } elseif (is_array($value)) {
            return array_map([$this, 'convertArray'], $value);
        }

        return $value;
    }

    /**
     * Get all public properties of class.
     * @return array
     */
    public static function publicProperties(): array
    {
        // Get all public propertes.
        $reflectionProperties = (new ReflectionClass(static::class))->getProperties(ReflectionProperty::IS_PUBLIC);
        return array_map(function (ReflectionProperty $reflectionProperty) {
            return $reflectionProperty->getName();
        }, $reflectionProperties);
    }
}
