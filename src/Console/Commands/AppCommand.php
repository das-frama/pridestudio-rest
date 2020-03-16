<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Base\AbstractCommand;
use App\Services\SystemService;

/**
 * AppCommand class.
 * Example: App init
 */
class AppCommand extends AbstractCommand
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
            $this->line('Public storage symlink created.');
        }

        // Generate JWT secret key.
        if ($this->putJWTSecret(random_string(16))) {
            $this->line('JWT secret stored.');
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
            return false;
        }
        return symlink($target, $link);
    }

    /**
     * Generate JWT secret and put it in .env file.
     * @param string $secret
     * @return bool
     */
    protected function putJWTSecret(string $secret): bool
    {
        $env = APP_DIR . DIRECTORY_SEPARATOR . '.env';
        if (!file_exists($env)) {
            return false;
        }
        $currentSecret = getenv('JWT_SECRET');
        if (!empty($currentSecret)) {
            return false;
        }

        $content = file_get_contents($env);
        $key = 'JWT_SECRET=';
        return (bool)file_put_contents($env, str_replace($key . $currentSecret, $key . $secret, $content));
    }

    private function getData(string $filename): array
    {
        $path = join(DIRECTORY_SEPARATOR, ['data', 'init', $filename]);
        return require APP_DIR . DIRECTORY_SEPARATOR . $path;
    }
}
