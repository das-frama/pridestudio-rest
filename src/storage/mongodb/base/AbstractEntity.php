<?php

declare(strict_types=1);

namespace app\storage\mongodb\base;

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
    protected $exclude = [];
    protected $unserialized = false;

    public function setInclude(array $properties): void
    {
        $this->include = $properties;
    }

    public function setExclude(array $properties): void
    {
        $this->exclude = $properties;
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
            $value = $reflectionProperty->getValue($this);
            $isInclude = empty($this->include) || in_array($name, $this->include);
            $isExclude = !empty($this->exclude) && in_array($name, $this->exclude);
            if ($isInclude && !$isExclude) {
                $properties[$name] = $value;
            }
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