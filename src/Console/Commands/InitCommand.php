<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SystemService;

/**
 * InitCommand class.
 * Example: App init
 */
class InitCommand
{
    private SystemService $service;

    /**
     * InitCommand constructor.
     * @param SystemService $service
     */
    public function __construct(SystemService $service)
    {
        $this->service = $service;
    }

    public function main(): int
    {
        // Settings.
        $data = $this->getData('settings.php');
        if ($this->service->initSettings($data)) {
            fwrite(STDOUT, "Settings init successfull.\n");
        } else {
            fwrite(STDOUT, "Settings already initiated.\n");
        }
        // Halls.
        if ($this->service->initHalls()) {
            fwrite(STDOUT, "Halls init successfull.\n");
        } else {
            fwrite(STDOUT, "Halls already initiated.\n");
        }
        // Coupons.
        if ($this->service->initCoupons()) {
            fwrite(STDOUT, "Coupons init successfull.\n");
        } else {
            fwrite(STDOUT, "Coupons already initiated.\n");
        }

        return 0;
    }

    private function getData(string $filename): array
    {
        $path = join(DIRECTORY_SEPARATOR, ['data', 'init', $filename]);
        return require(APP_DIR . DIRECTORY_SEPARATOR . $path);
    }
}
