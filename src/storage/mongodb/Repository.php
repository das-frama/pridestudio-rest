<?php

declare(strict_types=1);

namespace app\storage\mongodb;

use MongoDB\Client;
use MongoDB\Collection;

abstract class Repository
{
    /** @var Collection */
    protected $collection;

    /** @var array */
    protected $defaultOptions = [];

    public function __construct(Client $client, string $collectionName)
    {
        $this->collection = $client
            ->selectDatabase('pridestudio')
            ->selectCollection($collectionName);
    }

    /**
     * Finds an entity from storage by filter.
     * @param array $filter
     * @param array $include
     * @param array $exclude
     * @return array|object|null
     */
    protected function internalFindOne(array $filter, array $include = [], array $exclude = []): ?Entity
    {
        // Prepare filter.
        if (isset($filter['id'])) {
            $filter['_id'] = new ObjectId($filter['id']);
            unset($filter['id']);
        }

        // Prepare options.
        $options = $this->defaultOptions;
        if (!empty($include)) {
            $options['projection'] = array_fill_keys($include, 1);
        } elseif (!empty($exclude)) {
            $options['projection'] = array_fill_keys($exclude, 0);
        }
        if (isset($options['projection']['id'])) {
            $options['projection']['_id'] = $options['projection']['id'];
            unset($options['projection']['id']);
        }

        // Process result.
        $entity = $this->collection->findOne($filter, $options);
        if (!$entity instanceof Entity) {
            return null;
        }
        $entity->setInclude($include);
        $entity->setExclude($exclude);
        return $entity;
    }

    /**
     * @param array $link ['as' => ['localField' => 'foreignCollection.field']
     * @return Entity[]
     */
    public function findWith(array $links, array $filter, array $include = [], array $exclude = []): array
    {
        $pipeline = [];

        // Construct filter.
        if (isset($filter['id'])) {
            $filter['_id'] = new ObjectId($filter['id']);
            unset($filter['id']);
        }
        $pipeline[] = ['$match' => $filter];

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
        if (!empty($exclude)) {
            $include = array_diff_key($include, $exclude);
        }
        if (!empty($include)) {
            $project = array_fill_keys($include, 1);
            if (isset($include['id'])) {
                $project['_id'] = $project['id'];
                unset($project['id']);
            }
            $pipeline[] = ['$project' => $project];
        }

        // Perform query.
        $cursor = $this->collection->aggregate($pipeline, $this->defaultOptions);
        $entities = $cursor->toArray();
        foreach ($entities as $entity) {
            $entity->setInclude($include);
            $entity->setExclude($exclude);
        }
        return $entities;
    }
}
