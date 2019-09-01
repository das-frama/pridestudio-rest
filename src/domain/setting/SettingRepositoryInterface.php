<?php

declare(strict_types=1);

namespace app\domain\setting;

use app\entity\Setting;

interface SettingRepositoryInterface
{
    /**
     * @return Setting[]
     */
    public function findByRegEx(string $regex, bool $onlyActive): array;
    public function findByKey(string $key, bool $onlyActive): ?Setting;

    /**
     * @return Setting[]
     */
    public function findAll(bool $onlyActive): array;
    public function save(): bool;
}
