<?php

declare(strict_types=1);

namespace app\domain\hall;

use app\entity\Hall;
use app\entity\Service;
use app\storage\mongodb\HallRepository;

class HallService
{
    /** @var HallRepositoryInterface */
    private $hallRepo;

    public function __construct(HallRepository $hallRepo)
    {
        $this->hallRepo = $hallRepo;
    }

    /**
     * Get hall by id.
     * @param string $id
     * @param array $params
     * @return Hall|null
     */
    public function findByID(string $id, array $params = []): ?Hall
    {
        $include = $params['include'] ?? [];
        $exclude = $params['exclude'] ?? [];
        return $this->hallRepo->findOne(['id' => $id], true, $include, $exclude);
    }

    /**
     * Get hall by slug.
     * @param string $slug
     * @param array $params
     * @return Hall|null
     */
    public function findBySlug(string $slug, array $params = []): ?Hall
    {
        $filter = ['slug' => $slug];
        $include = $params['include'] ?? [];
        $exclude = $params['exclude'] ?? [];
        if (in_array('services_object', $include)) {
            return $this->hallRepo->findWithServices($filter, true, $include, $exclude);
        }
        return $this->hallRepo->findOne($filter, true, $include, $exclude);
    }

    /** 
     * Find services in hall.
     * @param string $slug
     * @param array $params
     * @return Service[]
     */
    public function findServices(string $slug, array $params = []): array
    {
        $selected = $params['selected'] ?? [];
        $include  = $params['include'] ?? [];
        $exclude  = $params['exclude'] ?? [];
        return $this->hallRepo->findServices(['slug' => $slug], true, $selected, $include, $exclude);
    }

    /**
     * Get an ID of hall by slug.
     * @param string $slug
     * @return string|null
     */
    public function getIDBySlug(string $slug): ?string
    {
        $hall = $this->hallRepo->findOne(['slug' => $slug], true, ['id' => 1]);
        if ($hall === null) {
            return null;
        }
        return $hall->id;
    }

    /**
     * Get all halls.
     * @param int $limit
     * @param int $offset
     * @param array $params
     * @return Hall[]
     */
    public function findAll(int $limit, int $offset, array $params = []): array
    {
        $include = $params['include'] ?? [];
        $exclude = $params['exclude'] ?? [];
        return $this->hallRepo->findAll($limit, $offset, true, $include, $exclude);
    }

    /**
     * Check if hall is exists by provided slug.
     * @param string $slug
     * @param bool $onlyActive
     * @return bool
     */
    public function isExists(string $slug, $onlyActive = true): bool
    {
        return $this->hallRepo->isExists(['slug' => $slug], $onlyActive);
    }
}
