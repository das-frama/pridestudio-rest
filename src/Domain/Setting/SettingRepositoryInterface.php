<?php

declare(strict_types=1);

namespace App\Domain\Setting;

use App\Domain\CommonRepositoryInterface;
use App\Entity\Setting;

interface SettingRepositoryInterface extends CommonRepositoryInterface
{
    /**
     * @return Setting[]
     */
    public function findByRegEx(string $regex, bool $onlyActive, array $include = []): array;
    public function insertManyIfNotExists(array $settings): int;
}
