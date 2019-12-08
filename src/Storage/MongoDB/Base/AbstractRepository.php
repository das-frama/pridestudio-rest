<?php

declare(strict_types=1);

namespace App\Storage\MongoDB\Base;

use App\Domain\CommonRepositoryInterface;
use Exception;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\BSON\Regex;
use MongoDB\BSON\ObjectId;

abstract class AbstractRepository implements CommonRepositoryInterface
{
    protected Database $database;
    protected Collection $collection;
    protected array $defaultOptions = [];

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
        $Entity = $this->collection->findOne($this->convertFilter($filter), $options);
        if (!$Entity instanceof AbstractEntity) {
            return null;
        }
        $Entity->setInclude($include);
        return $Entity;
    }

    /**
     * {@inheritDoc}
     */
    public function findOneAndUpdate(array $filter, AbstractEntity $Entity, array $include = [], bool $returnNew = false): ?AbstractEntity
    {
        // Prepare update.
        if (property_exists($Entity, 'updated_at')) {
            $Entity->updated_at = time();
        }
        $update = [
            '$set' => $Entity,
        ];
        $options = $this->defaultOptions;
        // Prepare projection.
        $options['projection'] = empty($include) ? [] : array_fill_keys($include, 1);
        if (isset($options['projection']['id'])) {
            $options['projection']['_id'] = $options['projection']['id'];
            unset($options['projection']['id']);
        }
        $options['returnNewDocument'] = $returnNew;
        // Process result.
        $Entity = $this->collection->findOneAndUpdate($this->convertFilter($filter), $update, $options);
        if (!$Entity instanceof AbstractEntity) {
            return null;
        }
        $Entity->setInclude($include);
        return $Entity;
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
        return array_map(function (AbstractEntity $Entity) use ($include) {
            $Entity->setInclude($include);
            return $Entity;
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
    public function insert(AbstractEntity $Entity): ?AbstractEntity
    {
        if (property_exists($Entity, 'updated_at')) {
            $Entity->updated_at = time();
        }
        $result = $this->collection->insertOne($Entity, [
            'bypassDocumentValidation' => false,
        ]);
        if (!$result->isAcknowledged()) {
            return null;
        }
        $Entity->id = (string) $result->getInsertedId();
        return $Entity;
    }

    /**
     * {@inheritDoc}
     */
    public function update(AbstractEntity $Entity, bool $upsert = false): ?AbstractEntity
    {
        if ($Entity->id === null) {
            return null;
        }
        $filter = ['_id' => new ObjectId($Entity->id)];
        if (property_exists($Entity, 'updated_at')) {
            $Entity->updated_at = time();
        }
        $update = ['$set' => $Entity];
        $result = $this->collection->updateOne($filter, $update, [
            'bypassDocumentValidation' => false,
            'upsert' => $upsert,
        ]);
        return $result->isAcknowledged() ? $Entity : null;
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
            // $bsonFilter['_id'] = $bsonFilter['id'];
            try {
                if (is_array($bsonFilter['id'])) {
                    $bsonFilter['_id'] = ['$in' => array_map(function (string $id) {
                        return new ObjectId($id);
                    }, $bsonFilter['id'])];
                } else {
                    $bsonFilter['_id'] = new ObjectId($bsonFilter['id']);
                }
            } catch (Exception $e) {
                return $bsonFilter;
            }
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
