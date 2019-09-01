<?php

declare(strict_types=1);

namespace app\domain\hall;

use app\entity\Hall;
use app\storage\mongodb\HallRepository;

class HallService
{
    /**
     * @var HallRepositoryInterface
     */
    private $hallRepo;

    public function __construct(HallRepository $hallRepo)
    {
        $this->hallRepo = $hallRepo;
    }

    /**
     * @return Hall|null
     */
    public function findByID(string $id): ?Hall
    {
        return $this->hallRepo->findByID($id);
    }

    /**
     * @return Hall|null
     */
    public function findBySlug(string $slug, array $params = []): ?Hall
    {
        $includeColumns = $this->getColumns($params);
        $hall = $this->hallRepo->findBySlug($slug, true, $includeColumns);
        return $hall;
    }

    /**
     * @return Hall[]
     */
    public function findAll(int $limit, int $offset, array $params = []): array
    {
        $includeColumns = $this->getColumns($params);
        return $this->hallRepo->findAll($limit, $offset, true, $includeColumns);
    }

    private function getColumns(array $params): array
    {
        if (!isset($params['include'])) {
            return [];
        }
        return explode(',', $params['include']);
    }
}