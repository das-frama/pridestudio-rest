<?php

declare(strict_types=1);

namespace app\domain\setting;

use app\domain\CommonRepositoryInterface;
use app\entity\Setting;

interface SettingRepositoryInterface extends CommonRepositoryInterface
{
    /**
     * @return Setting[]
     */
    public function findByRegEx(string $regex, bool $onlyActive, array $include = []): array;
    public function insertManyIfNotExists(array $settings): int;
}
