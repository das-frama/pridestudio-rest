<?php
declare(strict_types=1);

namespace App\Repositories\Base;

use App\Entities\Base\AbstractEntity;
use App\Models\Pagination;
use Exception;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

/**
 * Class AbstractRepository
 * @package App\Repositories\Base
 */
abstract class AbstractRepository implements CommonRepositoryInterface
{
    public array $sort = ['_id' => 1];

    protected Database $database;
    protected Collection $collection;
    protected array $relations = [];
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
    public function findOne(array $filter, array $with = []): ?AbstractEntity
    {
        $record = $this->collection->findOne($this->convertFilter($filter), $this->defaultOptions);
        return $record instanceof AbstractEntity ? $record : null;
    }

    /**
     * Convert filter to bson object.
     * @param array $filter
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
            try {
                if (is_array($bsonFilter['id'])) {
                    $bsonFilter['_id'] = [
                        '$in' => array_map(function (string $id) {
                            return new ObjectId($id);
                        }, $bsonFilter['id'])
                    ];
                } else {
                    $bsonFilter['_id'] = new ObjectId($bsonFilter['id']);
                }
            } catch (Exception $e) {
                return $bsonFilter;
            }
            unset($bsonFilter['id']);
        }
        if (isset($filter['$or'])) {
            return [
                '$or' => array_map(function ($key, $column) {
                    return [$key => $column];
                }, array_keys($bsonFilter), $bsonFilter)
            ];
        }
        return $bsonFilter;
    }

    /**
     * {@inheritDoc}
     */
    public function findOneAndUpdate(array $filter, AbstractEntity $entity, bool $returnNew = false): ?AbstractEntity
    {
        // Prepare update.
        if (property_exists($entity, 'updated_at')) {
            $entity->updated_at = time();
        }
        $update = [
            '$set' => $entity,
        ];
        $options = $this->defaultOptions;
        $options['returnNewDocument'] = $returnNew;
        // Process result.
        $entity = $this->collection->findOneAndUpdate($this->convertFilter($filter), $update, $options);
        return $entity instanceof AbstractEntity ? $entity : null;
    }

    /**
     * {@inheritDoc}
     */
    public function findPaginated(Pagination $pagination, array $filter = [], array $with = []): array
    {
//        $filter = array_map(function ($value) {
//            $str = (string)$value;
//            $first = substr($str, 0, 1);
//            $last = substr($str, -1);
//            if ($first === '%' && $last === '%') {
//                $str = substr($str, 1, -1);
//            } elseif ($last === '%') {
//                $str = '^' . substr($str, 0, -1);
//            } elseif ($first === '%') {
//                $str = substr($str, 1) . '$';
//            } else {
//                return $str;
//            }
//            return new Regex($str, 'i');
//        }, $filter);
        $options = array_merge($this->defaultOptions, [
            'limit' => $pagination->limit,
            'skip' => $pagination->skip(),
            'sort' => $this->sort,
        ]);

        return $this->collection->find($this->convertFilter($filter), $options)->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(array $filter = [], array $with = []): array
    {
        return $this->collection->find($this->convertFilter($filter), $this->defaultOptions)->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function count(array $filter = []): int
    {
        return $this->collection->countDocuments($this->convertFilter($filter));
    }

    /**
     * {@inheritDoc}
     */
    public function isExists(array $filter): bool
    {
        return (bool)$this->collection->countDocuments($this->convertFilter($filter));
    }

    /**
     * {@inheritDoc}
     */
    public function insert(AbstractEntity $entity): ?AbstractEntity
    {
        if (property_exists($entity, 'updated_at')) {
            $entity->updated_at = time();
        }
        $result = $this->collection->insertOne($entity, [
            'bypassDocumentValidation' => false,
        ]);
        if (!$result->isAcknowledged()) {
            return null;
        }

        $entity->id = (string)$result->getInsertedId();
        return $entity;
    }

    /**
     * {@inheritDoc}
     */
    public function update(AbstractEntity $entity, bool $upsert = false): ?AbstractEntity
    {
        if ($entity->id === null) {
            return null;
        }
        $filter = ['_id' => new ObjectId($entity->id)];
        if (property_exists($entity, 'updated_at')) {
            $entity->updated_at = time();
        }
        $update = ['$set' => $entity];
        $result = $this->collection->updateOne($filter, $update, [
            'bypassDocumentValidation' => false,
            'upsert' => $upsert,
        ]);
        return $result->isAcknowledged() ? $entity : null;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $id): bool
    {
        $result = $this->collection->deleteOne(['_id' => new ObjectId($id)]);
        return (bool)$result->getDeletedCount();
    }

    /**
     * @param AbstractEntity $entity
     * @param array $with
     * @return AbstractEntity
     */
    protected function withRelations(AbstractEntity $entity, array $with): AbstractEntity
    {
        return $entity;
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

        return (bool)$result;
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
