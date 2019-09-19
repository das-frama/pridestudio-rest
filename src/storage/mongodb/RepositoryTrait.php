<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use Mongodb\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Database;

trait RepositoryTrait
{
    /** @var Database */
    private $database;

    /** @var Collection */
    private $collection;

    /** @var array */
    private $defaultOptions = [];

    /**
     * Finds an entity from storage by filter.
     * @param array $filter
     * @param array $include
     * @return Entity|null
     */
    private function internalFindOne(array $filter, array $options, array $include = []): ?Entity
    {
        // Prepare projection.
        $projection = empty($include) ? [] : array_fill_keys($include, 1);
        if (isset($projection['id'])) {
            $projection['_id'] = $projection['id'];
            unset($projection['id']);
        }
        // Process result.
        $entity = $this->collection->findOne(
            $this->convertFilter($filter),
            array_merge($options, ['projection' => $projection])
        );
        if (!$entity instanceof Entity) {
            return null;
        }
        $entity->setInclude($include);
        return $entity;
    }

    /**
     * @param array $link ['as' => ['localField' => 'foreignCollection.field']
     * @return Entity[]
     */
    private function internalFindWith(array $links, array $filter, array $options, array $include = []): array
    {
        $pipeline = [];

        // Construct filter.
        $pipeline[] = ['$match' =>  $this->convertFilter($filter)];

        // Construct lookup.
        foreach ($links as $as => $link) {
            $localField = array_key_first($link);
            list($from, $foreignField) = explode('.', $link[$localField], 2);
            $pipeline[] = ['$lookup' =>  [
                'from' => $from,
                'localField' => $localField,
                'foreignField' => $foreignField,
                'as' => $as
            ]];
        }
        // Construct project.
        if (!empty($include)) {
            $project = array_fill_keys($include, 1);
            if (isset($include['id'])) {
                $project['_id'] = $project['id'];
                unset($project['id']);
            }
            $pipeline[] = ['$project' => $project];
        }

        // Perform query.
        $cursor = $this->collection->aggregate($pipeline, $options);
        return array_map(function (Entity $entity) use ($include) {
            $entity->setInclude($include);
            return $entity;
        }, $cursor->toArray());
    }

    /**
     * Find all entities.
     * @param array $filter
     * @param array $options
     * @param array $include
     * @return Entity[]
     */
    private function internalFindAll(array $filter = [], array $options, array $include = []): array
    {
        // Prepare projection.
        $projection = [];
        if (!empty($include)) {
            $projection = ['projection' => array_fill_keys($include, 1)];
            if (isset($projection['projection']['id'])) {
                $projection['projection']['_id'] = $projection['projection']['id'];
                unset($projection['projection']['id']);
            }
        }
        $sort = ['sort' => ['sort' => 1]];
        // Perform query.
        $cursor = $this->collection->find(
            $this->convertFilter($filter),
            array_merge($options, $projection, $sort)
        );
        return array_map(function (Entity $entity) use ($include) {
            $entity->setInclude($include);
            return $entity;
        }, $cursor->toArray());
    }

    /**
     * Convert filter to bson object.
     * @return array
     */
    private function convertFilter(array $filter): array
    {
        $bsonFilter = $filter;
        // Change id => _id.
        if (isset($bsonFilter['id'])) {
            $bsonFilter['_id'] = new ObjectId($bsonFilter['id']);
            unset($bsonFilter['id']);
        }
        return $bsonFilter;
    }

    /**
     * Convert array of string ids to array of ObjectId.
     * @param array $ids
     * @return ObjectId[]
     */
    private function convertToObjectId(array $ids): array
    {
        return array_map(function ($id) {
            return new ObjectId($id);
        }, $ids);
    }

    /**
     * Create schema validation.
     * @param string $collection
     * @param array $fields
     * @param array $required
     * @return bool
     */
    private function createSchemaValidation(string $collection, array $fields, array $required = []): bool
    {
        $properties = [];
        foreach ($fields as $field => $type) {
            $properties[$field] = [
                'bsonType' => $type,
                'description' => 'must be a ' . $field,
            ];
        }
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
    private function hasIndex(string $key): bool
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
