<?php

declare(strict_types=1);

namespace app\domain\auth;

use app\domain\user\UserRepositoryInterface;
use app\entity\User;
use Exception;
use Firebase\JWT\JWT;

class AuthService
{
    /** @var UserRepositoryInterface */
    private $userRepo;

    /** @var string */
    private $jwtSecret;

    private $jwtAllowedAlgs = ['HS256', 'HS384', 'HS512'];

    public function __construct(UserRepositoryInterface $userRepo, string $jwtSecret)
    {
        $this->userRepo = $userRepo;
        $this->jwtSecret = $jwtSecret;
    }
    
    /**
     * Login user.
     * @param string $username
     * @param string $password
     * @param int $expiresAt
     * @return array
     */
    public function login(string $username, string $password, int $expiresAt): array
    {
        $user = $this->userRepo->findOne(['email' => $username]);
        if ($user === null) {
            return [];
        }
        if ($user instanceof User) {
            if (!$user->verifyPassword($password)) {
                return [];
            }
        }

        // Generate csrf token.
        $csrf = $this->generateCSRF(8);
        return [
            $csrf,
            $this->generateJWT($user->id, $this->jwtSecret, $expiresAt, $csrf),
        ];
    }

    /**
     * Get user by jwt.
     * @param string $jwt
     * @return User|null
     */
    public function getUserByJWT(string $jwt): ?User
    {
        try {
            $claims = (array) JWT::decode($jwt, $this->jwtSecret, $this->jwtAllowedAlgs);
        } catch (Exception $e) {
            return null;
        }
        if (empty($claims['sub'])) {
            return null;
        }

        return $this->userRepo->findOne(['id' => $claims['sub']], ['id', 'email', 'name']);
    }

    /**
     * Generate CSRF token from length.
     * @param int $len
     * @return string
     */
    public function generateCSRF(int $len): string
    {
        return bin2hex(random_bytes($len));
    }

    /**
     * Generate JWT.
     * @param string $sub
     * @param string $secret
     * @param int $duration
     * @param string $csrf token
     * @return string
     */
    private function generateJWT(string $sub, string $secret, int $duration, string $csrf): string
    {
        $time = time();
        $token = [
            'iat' => $time,
            'exp' => $time + $duration,
            'sub' => $sub,
            'csrf' => $csrf,
        ];

        return JWT::encode($token, $secret, 'HS256');
    }
}
