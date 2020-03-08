<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\Hall;
use App\Entities\Service;
use App\Repositories\ServiceRepositoryInterface;

/**
 * Class ServiceService
 * @package App\Services
 */
class ServiceService
{
    private ServiceRepositoryInterface $repo;

    /**
     * ServiceService constructor.
     * @param ServiceRepositoryInterface $repo
     */
    public function __construct(ServiceRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Get service by id.
     * @param string $id
     * @param array $include
     * @return Service|null
     */
    public function find(string $id, array $include = []): ?Service
    {
        $service = $this->repo->findOne(['id' => $id], $include);
        return $service instanceof Service ? $service : null;
    }

    /**
     * Get all halls.
     * @param array $params (skip, limit, page, query)
     * @param array $include
     * @return Hall[]
     */
    public function findAll(array $params = [], array $include = []): array
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
        if (isset($params['query'])) {
            $filter = array_fill_keys(['name', 'slug'], $params['query']);
            return $this->repo->findPaginated($filter, $limit, $skip, $sort, $include);
        }
        return $this->repo->findAll($filter, $limit, $skip, $sort, $include);
    }

    /**
     * Count halls.
     * @return int
     */
    public function count()
    {
        return $this->repo->count();
    }
}
