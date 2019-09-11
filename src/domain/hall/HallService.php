<?php

declare(strict_types=1);

namespace app\domain\hall;

use app\entity\Hall;
use app\entity\Service;
use app\entity\ServiceChild;
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
     * @param array $include
     * @param array $exclude
     * @return Hall|null
     */
    public function findByID(string $id, array $include = [], array $exclude = []): ?Hall
    {
        $filter = [
            'id' => $id,
            'is_active' => true,
        ];
        return $this->hallRepo->findOne($filter, $include, $exclude);
    }

    /**
     * Get hall by slug.
     * @param string $slug
     * @param array $join
     * @param array $include
     * @param array $exclude
     * @return Hall|null
     */
    public function findBySlug(string $slug, array $include = [], array $exclude = []): ?Hall
    {
        $filter = [
            'slug' => $slug,
            'is_active' => true,
        ];
        return $this->hallRepo->findOne($filter, $include, $exclude);
    }

    /** 
     * Find services in hall.
     * @param string $slug
     * @param array $include
     * @param array $exclude
     * @return Service[]
     */
    public function findServices(string $slug, array $selected = [], array $include = [], array $exclude = []): array
    {
        $filter = [
            'slug' => $slug,
            'is_active' => true,
        ];
        if (empty($selected)) {
            $hall = $this->hallRepo->findOne($filter, ['services']);
            if ($hall === null) {
                return null;
            }
            $selected = $hall->getDefaultServices();
        }
        return $this->hallRepo->findServices($filter, $selected, $include, $exclude);
    }

    /**
     * Get an ID of hall by slug.
     * @param string $slug
     * @return string|null
     */
    public function getIDBySlug(string $slug): ?string
    {
        $filter = [
            'slug' => $slug,
            'is_active' => true
        ];
        $hall = $this->hallRepo->findOne($filter, ['id' => 1]);
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
