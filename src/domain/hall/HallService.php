<?php

declare(strict_types=1);

namespace app\domain\hall;

use app\entity\Hall;
use app\entity\Service;
use app\storage\mongodb\HallRepository;

class HallService
{
    /** @var HallRepositoryInterface */
    public $hallRepo;

    public function __construct(HallRepository $hallRepo)
    {
        $this->hallRepo = $hallRepo;
    }

    /**
     * Get hall by id.
     * @param string $id
     * @param array $include
     * @return Hall|null
     */
    public function findByID(string $id, array $include = []): ?Hall
    {
        $filter = [
            'id' => $id,
            'is_active' => true,
        ];
        return $this->hallRepo->findOne($filter, $include);
    }

    /**
     * Get hall by slug.
     * @param string $slug
     * @param array $include
     * @return Hall|null
     */
    public function findBySlug(string $slug, array $include = []): ?Hall
    {
        $filter = [
            'slug' => $slug,
            'is_active' => true,
        ];
        return $this->hallRepo->findOne($filter, $include);
    }

    /** 
     * Find services in hall.
     * @param string $slug
     * @param array $include
     * @return Service[]
     */
    public function findServices(string $slug, array $selected = [], array $include = []): array
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
        return $this->hallRepo->findServices($filter, $selected, $include);
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
     * @param array $include
     * @return Hall[]
     */
    public function findAll(array $include = []): array
    {
        return $this->hallRepo->findAll(['is_active' => true], $include);
    }

    /**
     * Check if hall is exists by provided slug.
     * @param string $slug
     * @param bool $onlyActive
     * @return bool
     */
    public function isExists(string $slug, $onlyActive = true): bool
    {
        $filter = ['slug' => $slug];
        if ($onlyActive) {
            $filter['is_active'] = true;
        }
        return $this->hallRepo->isExists($filter);
    }
}
