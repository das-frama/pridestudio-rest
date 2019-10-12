<?php

declare(strict_types=1);

namespace app\storage\mongodb\base;

use app\domain\CommonRepositoryInterface;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\BSON\Regex;
use MongoDB\BSON\ObjectId;

abstract class AbstractRepository implements CommonRepositoryInterface
{
    /** @var Database */
    protected $database;

    /** @var Collection */
    protected $collection;

    /** @var array */
    protected $defaultOptions = [];

    /**
     * Constructor.
     * @param string $database
     * @param string $collection
     * @param Client $client
     */
    public function __construct(string $database, string $collection, Client $client)
    {
        $this->database = $client->selectDatabase($database);
        $this->collection = $this->database->selectCollection($collection);
    }
    
    /**
     * {@inheritDoc}
     */
    abstract public function init(): bool;

    /**
     * {@inheritDoc}
     */
    public function findOne(array $filter, array $include = []): ?AbstractEntity
    {
        $options = $this->defaultOptions;
        // Prepare projection.
        $options['projection'] = empty($include) ? [] : array_fill_keys($include, 1);
        if (isset($options['projection']['id'])) {
            $options['projection']['_id'] = $options['projection']['id'];
            unset($options['projection']['id']);
        }
        // Process result.
        $entity = $this->collection->findOne($this->convertFilter($filter), $options);
        if (!$entity instanceof AbstractEntity) {
            return null;
        }
        $entity->setInclude($include);
        return $entity;
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(array $filter, int $limit = 0, int $skip = 0, array $sort = [], array $include = []): array
    {
        $options = array_merge($this->defaultOptions, [
            'limit' => $limit,
            'skip' => $skip,
            'sort' => $sort,
        ]);
        // Prepare projection.
        if (!empty($include)) {
            $options['projection'] = array_fill_keys($include, 1);
            if (isset($options['projection']['id'])) {
                $options['projection']['_id'] = $options['projection']['id'];
                unset($options['projection']['id']);
            }
        }
        // Limit cursor.
        if (isset($options['limit']) && $options['limit'] == 0) {
            unset($options['limit']);
        }
        // Skip cursor.
        if (isset($options['skip']) && $options['skip'] == 0) {
            unset($options['skip']);
        }
        // Sort cursor.
        if (isset($options['sort']['id']) && $options['sort']['id']) {
            $options['sort']['_id'] = $options['sort']['id'];
            unset($options['sort']['id']);
        }

        // Perform query.
        $cursor = $this->collection->find($this->convertFilter($filter), $options);
        return array_map(function (AbstractEntity $entity) use ($include) {
            $entity->setInclude($include);
            return $entity;
        }, $cursor->toArray());
    }

    /**
     * {@inheritDoc}
     */
    public function search(array $search, int $limit = 0, int $skip = 0, array $sort = [], array $include = []): array
    {
        $filter = array_map(function ($value) {
            $str = (string) $value;
            $first = substr($str, 0, 1);
            $last = substr($str, -1);
            if ($first === '%' && $last === '%') {
                $str = substr($str, 1, -1);
            } elseif ($last === '%') {
                $str = '^' . substr($str, 0, -1);
            } elseif ($first === '%') {
                $str = substr($str, 1) . '$';
            } else {
                return $str;
            }
            return new Regex($str, 'i');
        }, $search);
        return $this->findAll(['$or' => $filter], $limit, $skip, $sort, $include);
    }

    /**
     * {@inheritDoc}
     */
    public function count(array $filter = []): int
    {
        return $this->collection->count($this->convertFilter($filter));
    }

    /**
     * {@inheritDoc}
     */
    public function isExists(array $filter): bool
    {
        return (bool) $this->collection->count($this->convertFilter($filter));
    }
    
    /**
     * {@inheritDoc}
     */
    public function insert(AbstractEntity $entity): ?string
    {
        $result = $this->collection->insertOne($entity, [
            'bypassDocumentValidation' => false,
        ]);
        $id = $result->getInsertedId();
        return ($id instanceof ObjectId) ? (string) $id : null;
    }

    /**
     * {@inheritDoc}
     */
    public function update(AbstractEntity $entity): bool
    {
        if ($entity->id === null) {
            return false;
        }
        $filter = ['_id' => new ObjectId($entity->id)];
        $update = ['$set' => $entity];
        $result = $this->collection->updateOne($filter, $update, [
            'bypassDocumentValidation' => false,
        ]);
        return $result->isAcknowledged();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $id): bool
    {
        $result = $this->collection->deleteOne(['_id' => new ObjectId($id)]);
        return (bool) $result->getDeletedCount();
    }

    /**
     * Convert filter to bson object.
     * @return array
     */
    protected function convertFilter(array $filter): array
    {
        $bsonFilter = $filter;
        if (isset($filter['$or'])) {
            $bsonFilter = $filter['$or'];
        }
        // Change id to _id.
        if (isset($bsonFilter['id'])) {
            $bsonFilter['_id'] = new ObjectId($bsonFilter['id']);
            unset($bsonFilter['id']);
        }
        if (isset($filter['$or'])) {
            return ['$or' => array_map(function ($key, $column) {
                return [$key => $column];
            }, array_keys($bsonFilter), $bsonFilter)];
        }
        return $bsonFilter;
    }

    /**
     * Convert array of string ids to array of ObjectId.
     * @param array $ids
     * @return ObjectId[]
     */
    protected function convertToObjectId(array $ids): array
    {
        return array_map(function ($id) {
            return new ObjectId($id);
        }, $ids);
    }

    /**
     * Create schema validation.
     * @param string $collection
     * @param array $properties
     * @param array $required
     * @return bool
     */
    protected function createSchemaValidation(string $collection, array $properties, array $required = []): bool
    {
        $result = $this->database->command([
            'collMod' => $collection,
            'validator' => [
                '$jsonSchema' => [
                    'bsonType' => 'object',
                    'required' => $required,
                    'properties' => $properties,
                ]
            ]
        ]);

        return (bool) $result;
    }

    /**
     * Check if collection has given index.
     * @param string $key
     * @return bool
     */
    protected function hasIndex(string $key): bool
    {
        $indexes = $this->collection->listIndexes();
        foreach ($indexes as $index) {
            $k = $index->getKey();
            if (isset($k[$key])) {
                return true;
            }
        }
        return false;
    }
}
