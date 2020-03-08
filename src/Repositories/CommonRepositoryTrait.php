<?php

declare(strict_types=1);

namespace App\Storage\MongoDB\Base;

use Mongodb\BSON\ObjectId;

/**
 * Trait CommonRepositoryTrait
 * @package App\Storage\MongoDB\Base
 */
trait CommonRepositoryTrait
{
    /**
     * Finds an Entity from storage by filter.
     * @param array $filter
     * @param array $options
     * @param array $include
     * @return AbstractEntity|null
     */
    private function internalFindOne(array $filter, array $options, array $include = []): ?AbstractEntity
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
        if (!$entity instanceof AbstractEntity) {
            return null;
        }
        $entity->setInclude($include);
        return $entity;
    }

    /**
     * Convert filter to bson object.
     * @param array $filter
     * @return array
     */
    private function convertFilter(array $filter): array
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
            return [
                '$or' => array_map(function ($key, $column) {
                    return [$key => $column];
                }, array_keys($bsonFilter), $bsonFilter)
            ];
        }
        return $bsonFilter;
    }

    /**
     * @param array $links
     * @param array $filter
     * @param array $options
     * @param array $include
     * @return AbstractEntity[]
     */
    private function internalFindWith(array $links, array $filter, array $options, array $include = []): array
    {
        $pipeline = [];

        // Construct filter.
        $pipeline[] = ['$match' => $this->convertFilter($filter)];

        // Construct lookup.
        foreach ($links as $as => $link) {
            $localField = array_key_first($link);
            list($from, $foreignField) = explode('.', $link[$localField], 2);
            $pipeline[] = [
                '$lookup' => [
                    'from' => $from,
                    'localField' => $localField,
                    'foreignField' => $foreignField,
                    'as' => $as
                ]
            ];
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
        return array_map(function (AbstractEntity $entity) use ($include) {
            $entity->setInclude($include);
            return $entity;
        }, $cursor->toArray());
    }

    /**
     * Search enitities.
     * @param array $search
     * @param array $include
     * @param array $options
     * @return AbstractEntity[]
     */
    private function internalSearch(array $search, array $include = [], array $options = []): array
    {
        $filter = array_map(function ($value) {
            $str = (string)$value;
            $first = substr($value, 0, 1);
            $last = substr($value, -1);
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

        return $this->internalFindAll(['$or' => $filter], $options, $include);
    }

    /**
     * Find all entities.
     * @param array $filter
     * @param array $options
     * @param array $include
     * @return AbstractEntity[]
     */
    private function internalFindAll(array $filter = [], array $options = [], array $include = []): array
    {
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
        $cursor = $this->collection->find(
            $this->convertFilter($filter),
            $options
        );

        return array_map(function (Entity $Entity) use ($include) {
            $Entity->setInclude($include);
            return $Entity;
        }, $cursor->toArray());
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
     * @param array $properties
     * @param array $required
     * @return bool
     */
    private function createSchemaValidation(string $collection, array $properties, array $required = []): bool
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
