<?php

declare(strict_types=1);

namespace app\domain\setting;

use app\entity\Setting;

interface SettingRepositoryInterface
{
    /**
     * @return Setting[]
     */
    public function findByRegEx(string $regex, bool $onlyActive, array $include = []): array;

    /**
     * Find setting by filter.
     * @param array $filter
     * @param array $include
     * @return Setting|null
     */
    public function findOne(array $filter, array $include = []): ?Setting;

    /**
     * Find all settings.
     * @param array $filter
     * @param array $include
     * @return Setting[]
     */
    public function findAll(array $filter = [], array $include = []): array;

    public function insertManyIfNotExists(array $settings): int;
    public function save(): bool;
}
