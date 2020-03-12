<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\Hall;
use App\Entities\Service;
use App\Repositories\HallRepositoryInterface;
use App\Services\Base\AbstractResourceService;

/**
 * Class HallService
 * @package App\Services
 */
class HallService extends AbstractResourceService
{
    /**
     * HallService constructor.
     * @param HallRepositoryInterface $repo
     */
    public function __construct(HallRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Get hall by slug.
     * @param string $slug
     * @return Hall|null
     */
    public function findBySlug(string $slug): ?Hall
    {
        $filter = [
            'slug' => $slug,
            'is_active' => true,
        ];
        $hall = $this->repo->findOne($filter);
        return $hall instanceof Hall ? $hall : null;
    }

    /**
     * Find services in hall.
     * @param string $id
     * @param array $selected
     * @return Service[]
     */
    public function findServices(string $id, array $selected = []): array
    {
        $filter = ['id' => $id];
        if (empty($selected)) {
            $hall = $this->repo->findOne($filter, ['services']);
            if ($hall === null) {
                return [];
            }
            $selected = $hall->getDefaultServices();
        }
        return $this->repo->findServices($filter, $selected);
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
        $hall = $this->repo->findOne($filter);
        return $hall instanceof Hall ? $hall->id : null;
    }

    /**
     * @param string $slug
     * @return bool
     */
    public function isUnique(string $slug): bool
    {
        return $this->repo->isExists(['slug' => $slug]);
    }
}
