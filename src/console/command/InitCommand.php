<?php

declare(strict_types=1);

namespace app\console\command;

use app\domain\system\SystemService;

/**
 * InitCommand class.
 * Example: app init
 */
class InitCommand
{
    /** @var SystemService */
    private $systemService;

    public function __construct(SystemService $systemService)
    {
        $this->systemService = $systemService;
    }

    public function main(): int
    {
        // Settings.
        $data = $this->getData('settings.php');
        if ($this->systemService->initSettings($data)) {
            fwrite(STDOUT, "Settings init successfull.\n");
        } else {
            fwrite(STDOUT, "Settings already initiated.\n");
        }
        // Halls.
        if ($this->systemService->initHalls()) {
            fwrite(STDOUT, "Halls init successfull.\n");
        } else {
            fwrite(STDOUT, "Halls already initiated.\n");
        }
        // Coupons.
        if ($this->systemService->initCoupons()) {
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
