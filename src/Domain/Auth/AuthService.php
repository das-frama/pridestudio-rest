<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\User\UserRepositoryInterface;
use App\Entity\User;
use Exception;
use Firebase\JWT\JWT;

/**
 * Class AuthService
 * @package App\Domain\Auth
 */
class AuthService
{
    private UserRepositoryInterface $userRepo;

    private string $jwtSecret;

    private array $jwtAllowedAlgs = ['HS256', 'HS384', 'HS512'];

    /**
     * AuthService constructor.
     * @param UserRepositoryInterface $userRepo
     * @param string $jwtSecret
     */
    public function __construct(UserRepositoryInterface $userRepo, string $jwtSecret)
    {
        $this->userRepo = $userRepo;
        $this->jwtSecret = $jwtSecret;
    }

    /**
     * Login user.
     * @param string $email
     * @param string $password
     * @return User|null
     * @throws Exception
     */
    public function login(string $email, string $password): ?User
    {
        // Find user.
        $user = $this->userRepo->findOne(['email' => $email]);
        if (!($user instanceof User)) {
            return null;
        }
        if (!$user->verifyPassword($password)) {
            return null;
        }
        return $user;
    }

    /**
     * Get user by jwt.
     * @param string $jwt
     * @return User|null
     */
    public function getUserByJWT(string $jwt): ?User
    {
        try {
            $claims = (array)JWT::decode($jwt, $this->jwtSecret, $this->jwtAllowedAlgs);
        } catch (Exception $e) {
            return null;
        }
        if (empty($claims['sub'])) {
            return null;
        }
        $user = $this->userRepo->findOne(['id' => $claims['sub']], ['id', 'email', 'name']);
        return $user instanceof User ? $user : null;
    }

    /**
     * Generate CSRF token from length.
     * @param int $len
     * @return string
     * @throws Exception
     */
    public function generateCSRF(int $len): string
    {
        return bin2hex(random_bytes($len));
    }

    /**
     * Get JWT.
     * @param User $user
     * @param int $expiresIn
     * @return string
     */
    public function getToken(User $user, int $expiresIn): string
    {
        return $this->generateJWT($user->id, $this->jwtSecret, $expiresIn);
    }

    /**
     * Generate JWT.
     * @param string $sub
     * @param string $secret
     * @param int $expiresIn
     * @return string
     */
    private function generateJWT(string $sub, string $secret, int $expiresIn): string
    {
        $time = time();
        $token = [
            'iat' => $time,
            'exp' => $time + $expiresIn,
            'sub' => $sub,
        ];

        return JWT::encode($token, $secret, 'HS256');
    }
}
