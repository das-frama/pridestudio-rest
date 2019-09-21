<?php

declare(strict_types=1);

namespace app\console\command;

use app\domain\user\UserService;

/**
 * SuperUserCommand class.
 * Example: app super-user super@user.com password123
 */
class SuperUserCommand
{
    /** @var UserService */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function main(string $email, string $password): int
    {
        // Admin user.
        if ($this->userService->initSuperUser($email, $password)) {
            fwrite(STDOUT, "Super user successfully created.\n");
        } else {
            fwrite(STDOUT, "Super user can't be created.\n");
        }

        return 0;
    }
}
