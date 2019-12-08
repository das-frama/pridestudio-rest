<?php

declare(strict_types=1);

namespace App\Console\Command;

use App\Domain\User\UserService;

/**
 * SuperUserCommand class.
 * Example: App super-user super@user.com password123
 */
class SuperUserCommand
{
    private UserService $userService;

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
