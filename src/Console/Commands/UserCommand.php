<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Entities\User;
use App\Repositories\UserRepositoryInterface;
use App\Services\ValidationService;

/**
 * UserCommand class.
 *
 * `php app user:init super@user.com password123`
 */
class UserCommand
{
    private UserRepositoryInterface $repo;

    public function __construct(UserRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param string $email
     * @param string $password
     * @return int
     */
    public function init(string $email, string $password): int
    {
        // Validate input.
        $errors = (new ValidationService())->validate([
            'email' => $email,
            'password' => $password,
        ], [
            'email' => ['required', 'email'],
            'password' => ['required', 'string:3:255'],
        ]);
        if (!empty($errors)) {
            fwrite(STDOUT, implode("\n", $errors));
            return -1;
        }

        // Check if user already exists.
        $user = $this->repo->findOne(['email' => $email]);
        if (!$user instanceof User) {
            $user = new User([
                'name' => 'Super Dude',
                'email' => $email,
                'role' => 'super',
                'is_active' => true,
            ]);
        }
        $user->setPassword($password);

        // Store user.
        $user = isset($user->id) ? $this->repo->update($user) : $this->repo->insert($user);
        if ($user === null) {
            fwrite(STDOUT, "Super user can't be created.\n");
            return -1;
        }

        fwrite(STDOUT, "Super user successfully created.\n");
        return 0;
    }
}
