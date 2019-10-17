<?php

declare(strict_types=1);

namespace app\domain\auth;

use app\domain\user\UserRepositoryInterface;
use app\entity\User;
use Firebase\JWT\JWT;

class AuthService
{
    /** @var UserRepositoryInterface */
    private $userRepo;

    /** @var string */
    private $jwtSecret;

    /** @var int */
    private $jwtDuration;

    public function __construct(UserRepositoryInterface $userRepo, string $jwtSecret, int $jwtDuration)
    {
        $this->userRepo = $userRepo;
        $this->jwtSecret = $jwtSecret;
        $this->jwtDuration = $jwtDuration;
    }
    
    /**
     * Login user.
     * @param string $username
     * @param string $password
     * @return string|null
     */
    public function login(string $username, string $password): ?string
    {
        $user = $this->userRepo->findOne(['email' => $username]);
        if ($user === null) {
            return null;
        }
        if ($user instanceof User) {
            if (!$user->verifyPassword($password)) {
                return null;
            }
        }

        return $this->generateJWT($user->id, $this->jwtSecret, $this->jwtDuration);
    }

    /**
     * Generate JWT.
     * @param string $sub
     * @param string $secret
     * @param int $duration
     * @return string
     */
    private function generateJWT(string $sub, string $secret, int $duration): string
    {
        $time = time();
        $token = [
            'iat' => $time,
            'exp' => $time + $duration,
            'sub' => $sub,
        ];

        return JWT::encode($token, $secret, 'HS256');
    }
}
