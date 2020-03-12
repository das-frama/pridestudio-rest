<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Setting;
use App\Repositories\Base\ResourceRepositoryInterface;

/**
 * Interface SettingRepositoryInterface
 * @package App\Repositories
 */
interface SettingRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * @param string $regex
     * @param bool $onlyActive
     * @param array $include
     * @return Setting[]
     */
    public function findByRegEx(string $regex, bool $onlyActive, array $include = []): array;

    /**
     * @param array $settings
     * @return int
     */
    public function insertManyIfNotExists(array $settings): int;
}
