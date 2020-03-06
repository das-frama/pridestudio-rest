<?php

declare(strict_types=1);

namespace App\Domain\Hall;

use App\Entity\Hall;
use App\Entity\Service;

class HallService
{
    private HallRepositoryInterface $hallRepo;

    public function __construct(HallRepositoryInterface $hallRepo)
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
        ];
        $hall = $this->hallRepo->findOne($filter, $include);
        return $hall instanceof Hall ? $hall : null;
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
        $hall = $this->hallRepo->findOne($filter, $include);
        return $hall instanceof Hall ? $hall : null;
    }

    /**
     * Find services in hall.
     * @param string $id
     * @param array $selected
     * @param array $include
     * @return Service[]
     */
    public function findServices(string $id, array $selected = [], array $include = []): array
    {
        $filter = [
            'id' => $id,
        ];
        if (empty($selected)) {
            $hall = $this->hallRepo->findOne($filter, ['services']);
            if ($hall === null) {
                return [];
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
     * @param array $params (skip, limit, page, query)
     * @param bool $onlyActive
     * @param array $include
     * @return Hall[]
     */
    public function findAll(array $params = [], $onlyActive = false, array $include = []): array
    {
        $page = intval($params['page'] ?? 0);
        $limit = intval($params['limit'] ?? 0);
        // Sort.
        $sort = [];
        if (isset($params['orderBy'])) {
            $sort[$params['orderBy']] = $params['ascending'] == 0 ? -1 : 1;
        } else {
            $sort['sort'] = 1;
        }
        // Skip.
        $skip = 0;
        if ($page > 0) {
            $skip = $limit * ($page - 1);
        }
        // Query.
        $filter = [];
        if ($onlyActive) {
            $filter['is_active'] = true;
        }
        if (isset($params['query'])) {
            $filter = array_fill_keys(['name', 'slug'], '%' . $params['query'] . '%');
            return $this->hallRepo->search($filter, $limit, $skip, $sort, $include);
        }
        return $this->hallRepo->findAll($filter, $limit, $skip, $sort, $include);
    }

    /**
     * Count halls.
     * @return int
     */
    public function count()
    {
        return $this->hallRepo->count();
    }

    /**
     * Check if hall is exists.
     * @param string $id
     * @param bool $onlyActive
     * @return bool
     */
    public function isExists(string $id, $onlyActive = true): bool
    {
        $filter = ['id' => $id];
        if ($onlyActive) {
            $filter['is_active'] = true;
        }
        return $this->hallRepo->isExists($filter);
    }

    /**
     * Create a new hall.
     * @param Hall $hall
     * @return string|null
     */
    public function create(Hall $hall): ?Hall
    {
        // Check uniqueness.
        if ($this->hallRepo->isExists(['slug' => $hall->slug])) {
            return null;
        }

        return $this->hallRepo->insert($hall);
    }

    /**
     * Update an existing hall.
     * @param Hall $hall
     * @return Hall|null
     */
    public function update(Hall $hall): ?Hall
    {
        $hall = $this->hallRepo->update($hall);
        return $hall instanceof Hall ? $hall : null;
    }

    /**
     * Delete an existing hall.
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        return $this->hallRepo->delete($id);
    }

    private function isUnique(string $slug): bool
    {
        $filter = ['slug' => $slug];
        return $this->hallRepo->isExists($filter);
    }
}
