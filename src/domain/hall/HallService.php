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
    public function findBySlug(string $slug): ?Hall
    {
        return $this->hallRepo->findBySlug($slug);
    }

    /**
     * @return Hall[]
     */
    public function findAll(int $limit, int $offset): array
    {
        return $this->hallRepo->findAll($limit, $offset);
    }
}
