<?php

declare(strict_types=1);

namespace app\domain\hall;

use app\entity\Hall;
use app\entity\HallService as AppHallService;
use app\entity\PriceRule;
use app\entity\Service;

class HallService
{
    /** @var HallRepositoryInterface */
    private $hallRepo;

    public function __construct(HallRepositoryInterface $hallRepo)
    {
        $this->hallRepo = $hallRepo;
    }

    public function load(array $data): Hall
    {
        $hall = new Hall;
        $hall->name = $data['name'] ?? null;
        $hall->slug = $data['slug'] ?? null;
        $hall->description = $data['description'] ?? null;
        $hall->base_price = $data['base_price'] ?? null;
        $hall->preview_image = $data['preview_image'] ?? null;
        $hall->sort = $data['sort'] ?? null;
        $hall->is_active = $data['is_active'] ?? null;
        if (is_array($data['services'])) {
            $hall->services = [];
            foreach ($data['services'] as $service) {
                $hallService = new AppHallService;
                $hallService->category_id = $service['category_id'] ?? null;
                $hallService->children = $service['children'] ?? [];
                $hall->services[] = $hallService;
            }
        }
        if (is_array($data['prices'])) {
            $hall->prices = [];
            foreach ($data['prices'] as $price) {
                $priceRule = new PriceRule;
                $priceRule->time_from = $price['time_from'] ?? null;
                $priceRule->time_to = $price['time_to'] ?? null;
                $priceRule->type = $price['type'] ?? null;
                $priceRule->from_length = $price['from_length'] ?? null;
                $priceRule->comparison = $price['comparison'] ?? null;
                $priceRule->price = $price['price'] ?? null;
                $priceRule->service_ids = $price['service_ids'] ?? [];
                $hall->prices[] = $priceRule;
            }
        }
        
        return $hall;
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
     * @param string $id
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
    public function create(Hall $hall): ?string
    {
        if ($hall->updated_at === null) {
            $hall->updated_at = time();
        }
        return $this->hallRepo->insert($hall);
    }

    /**
     * Update an existing hall.
     * @param Hall $hall
     * @return string|null
     */
    public function update(Hall $hall): ?string
    {
        return $this->hallRepo->update($hall) ? null : 'Error during update a record.';
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
}
