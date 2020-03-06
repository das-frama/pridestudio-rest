<?php

declare(strict_types=1);

namespace App\Domain\Setting;

use App\Domain\CommonRepositoryInterface;
use App\Entity\Setting;

interface SettingRepositoryInterface extends CommonRepositoryInterface
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
