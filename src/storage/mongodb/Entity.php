<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use JsonSerializable;
use ReflectionObject;
use ReflectionProperty;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Persistable;

/**
 * Entity is a base class for mongodb records.
 */
abstract class Entity implements Persistable, JsonSerializable
{
    protected $include = [];
    protected $exclude = [];

    public function setInclude(array $properties): void
    {
        $this->include = $properties;
    }

    public function setExclude(array $properties): void
    {
        $this->exclude = $properties;
    }

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
     * Provides an array or document to serialize as BSON
     * Called during serialization of the object to BSON. The method must return an array or stdClass.
     * Root documents (e.g. a MongoDB\BSON\Serializable passed to MongoDB\BSON\fromPHP()) will always be serialized as a BSON document.
     * For field values, associative arrays and stdClass instances will be serialized as a BSON document and sequential arrays (i.e. sequential, numeric indexes starting at 0) will be serialized as a BSON array.
     * @link https://php.net/manual/en/mongodb-bson-serializable.bsonserialize.php
     * @return array|object An array or stdClass to be serialized as a BSON array or document.
     */
    public function bsonSerialize(): array
    {
        return [
            '_id' => $this->id,
        ];
    }

    /**
     * Constructs the object from a BSON array or document
     * Called during unserialization of the object from BSON.
     * The properties of the BSON array or document will be passed to the method as an array.
     * @link https://php.net/manual/en/mongodb-bson-unserializable.bsonunserialize.php
     * @param array $data Properties within the BSON array or document.
     */
    public function bsonUnserialize(array $data): void
    {
        if ($data['_id'] instanceof ObjectId) {
            $this->id = $data['_id']->__toString();
            if (property_exists($this, 'created_at')) {
                $this->created_at = $data['_id']->getTimestamp();
            }
        }
        foreach ($data as $property => $value) {
            if (!property_exists($this, $property)) {
                continue;
            }
            if ($value instanceof ObjectId) {
                $this->{$property} = $value->__toString();
            } else if ($value instanceof UTCDateTime) {
                if (strpos($property, '_at', -3)) {
                    $this->{$property} = $value->toDateTime()->getTimestamp();
                } else {
                    $this->{$property} = $value->toDateTime();
                }
            } else {
                $this->{$property} = $value;
            }
        }
    }
}