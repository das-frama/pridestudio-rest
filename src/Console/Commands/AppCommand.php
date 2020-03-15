<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SystemService;

/**
 * AppCommand class.
 * Example: App init
 */
class AppCommand
{
    protected SystemService $service;

    /**
     * InitCommand constructor.
     * @param SystemService $service
     */
    public function __construct(SystemService $service)
    {
        $this->service = $service;
    }

    /**
     * @return int
     */
    public function init(): int
    {
        // Create public storage symlink.
        if ($this->createStorageSymlink()) {
            fwrite(STDOUT, "Public storage symlink created.\n");
        } else {
            fwrite(STDERR, "Could not create a symlink.\n");
        }

        // Settings.
//        $data = $this->getData('settings.php');
//        if ($this->service->initSettings($data)) {
//            fwrite(STDOUT, "Settings init successfully.\n");
//        } else {
//            fwrite(STDOUT, "Settings already initiated.\n");
//        }
//        // Halls.
//        if ($this->service->initHalls()) {
//            fwrite(STDOUT, "Halls init successfully.\n");
//        } else {
//            fwrite(STDOUT, "Halls already initiated.\n");
//        }
//        // Coupons.
//        if ($this->service->initCoupons()) {
//            fwrite(STDOUT, "Coupons init successfully.\n");
//        } else {
//            fwrite(STDOUT, "Coupons already initiated.\n");
//        }

        return 0;
    }

    /**
     * Create symlink for public storage.
     * @return bool
     */
    protected function createStorageSymlink(): bool
    {
        $target = join(DIRECTORY_SEPARATOR, [APP_DIR, 'storage', 'public']);
        $link = join(DIRECTORY_SEPARATOR, [APP_DIR, 'public', 'storage']);
        if (is_link($link)) {
            return true;
        }
        return symlink($target, $link);
    }

    private function getData(string $filename): array
    {
        $path = join(DIRECTORY_SEPARATOR, ['data', 'init', $filename]);
        return require APP_DIR . DIRECTORY_SEPARATOR . $path;
    }
}
