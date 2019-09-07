<?php

declare(strict_types=1);

namespace app\domain\hall;

use app\entity\Hall;
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
        $include = $this->getColumns('include', $params);
        $exclude = $this->getColumns('exclude', $params);
        return $this->hallRepo->findByID($id, true, $include, $exclude);
    }

    /**
     * Find hall with services.
     * @param string $id
     * @return Hall|null
     */
    public function findWithServices(string $id): ?Hall
    {
        return $this->hallRepo->findWithServices($id);
    }

    /**
     * Get hall by slug.
     * @param string $slug
     * @param array $params
     * @return Hall|null
     */
    public function findBySlug(string $slug, array $params = []): ?Hall
    {
        $include = $this->getColumns('include', $params);
        $exclude = $this->getColumns('exclude', $params);
        return $this->hallRepo->findBySlug($slug, true, $include, $exclude);
    }

    /**
     * Get an ID of hall by slug.
     * @param string $slug
     * @return string|null
     */
    public function getIDBySlug(string $slug): ?string
    {
        $hall = $this->hallRepo->findBySlug($slug, true, ['_id' => 1]);
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
        $includeColumns = $this->getColumns('include', $params);
        return $this->hallRepo->findAll($limit, $offset, true, $includeColumns);
    }

    /**
     * Return an array of coma separated fields.
     * @param string $key
     * @param array $params
     * @return array
     */
    private function getColumns(string $key, array $params): array
    {
        if (!isset($params[$key])) {
            return [];
        }
        return explode(',', $params[$key]);
    }
}
