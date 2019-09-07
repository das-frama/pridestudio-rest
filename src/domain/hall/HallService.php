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
        $include = $params['include'] ?? [];
        $exclude = $params['exclude'] ?? [];
        return $this->hallRepo->findByID($id, true, $include, $exclude);
    }

    /**
     * Get hall by slug.
     * @param string $slug
     * @param array $params
     * @return Hall|null
     */
    public function findBySlug(string $slug, array $params = []): ?Hall
    {
        $include = $params['include'] ?? [];
        $exclude = $params['exclude'] ?? [];
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
        $include = $params['include'] ?? [];
        $exclude = $params['exclude'] ?? [];
        return $this->hallRepo->findAll($limit, $offset, true, $include, $exclude);
    }
}
