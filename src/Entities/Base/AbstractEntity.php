<?php
declare(strict_types=1);

namespace App\Entities\Base;

use JsonSerializable;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Persistable;
use MongoDB\BSON\UTCDateTime;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;

/**
 * Entity is a base class for mongodb records.
 */
abstract class AbstractEntity implements Persistable, JsonSerializable
{
    protected array $fillable = [];
    protected array $include = [];
    protected array $expand = [];
    protected array $public = [];
    protected bool $unserialized = false;

    /**
     * AbstractEntity constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        if (!empty($params)) {
            $this->load($params);
        }
    }

    /**
     * Load array of data to the Entity.
     * Every Entity should implement this method to load the data.
     * @param array $data
     * @param array $safe
     * @return void
     */
    public function load(array $data, array $safe = []): void
    {
        if ($safe === []) {
            $safe = $this->fillable;
        }
        $reflection = (new ReflectionClass(static::class));
        foreach ($data as $key => $value) {
            $isSafe = empty($safe) || in_array($key, $safe);
            if (!property_exists($this, $key) || !$isSafe) {
                continue;
            }
            $property = $reflection->getProperty($key);
            $className = $property->getType()->getName();
            if (is_subclass_of($className, AbstractEntity::class)) {
                if (!$property->isInitialized($this)) {
                    $this->{$key} = new $className();
                }
                $this->{$key}->load($value);
            } else {
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
                if ($reflectionProperty->isInitialized($this)) {
                    $properties[$name] = $reflectionProperty->getValue($this);
                }
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
            if (!isset($this->{$property})) {
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
     * Get all public properties of class.
     * @return array
     */
    public static function publicProperties(): array
    {
        // Get all public properties.
        $reflectionProperties = (new ReflectionClass(static::class))->getProperties(ReflectionProperty::IS_PUBLIC);
        return array_map(function (ReflectionProperty $reflectionProperty) {
            return $reflectionProperty->getName();
        }, $reflectionProperties);
    }

    /**
     * {@inheritDoc}
     */
    public function bsonUnserialize(array $data): void
    {
        if (isset($data['_id']) && $data['_id'] instanceof ObjectId) {
            $this->id = (string)$data['_id'];
            if (property_exists($this, 'created_at')) {
                $this->created_at = $data['_id']->getTimestamp();
            }
        }
        foreach ($data as $property => $value) {
            if (!property_exists($this, (string)$property)) {
                continue;
            }
            if ($value instanceof ObjectId) {
                $this->{$property} = (string)$value;
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
            return (string)$value;
        } elseif (is_array($value)) {
            return array_map([$this, 'convertArray'], $value);
        }

        return $value;
    }
}
