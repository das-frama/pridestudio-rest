<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\User;
use App\Http\Resources\Auth\LoginResponseResource;
use App\Repositories\UserRepositoryInterface;
use Exception;
use Firebase\JWT\JWT;

/**
 * Class AuthService
 * @package App\Services
 */
class AuthService
{
    protected UserRepositoryInterface $repo;
    private string $jwtSecret;
    private int $jwtDuration;
    private array $jwtAllowedAlgs = ['HS256', 'HS384', 'HS512'];

    /**
     * AuthService constructor.
     * @param UserRepositoryInterface $repo
     * @param string $jwtSecret
     * @param int $jwtDuration
     */
    public function __construct(UserRepositoryInterface $repo, string $jwtSecret, int $jwtDuration)
    {
        $this->repo = $repo;
        $this->jwtSecret = $jwtSecret;
        $this->jwtDuration = $jwtDuration;
    }

    /**
     * Login user.
     * @param string $email
     * @param string $password
     * @return LoginResponseResource|null
     * @throws Exception
     */
    public function login(string $email, string $password): ?LoginResponseResource
    {
        // Find user.
        $user = $this->repo->findOne(['email' => $email]);
        if (!($user instanceof User)) {
            return null;
        }
        if (!$user->verifyPassword($password)) {
            return null;
        }

        $response = $this->generateTokens($user);
        $user->refresh_token = $response->refresh_token;
        if (!$this->repo->update($user)) {
            return null;
        }

        return $response;
    }

    /**
     * @param User $user
     * @return LoginResponseResource
     * @throws Exception
     */
    public function generateTokens(User $user): LoginResponseResource
    {
        $expiresIn = time() + $this->jwtDuration;
        return new LoginResponseResource([
            'access_token' => $this->getAccessToken($user, $expiresIn),
            'refresh_token' => $this->getRefreshToken(),
            'expires_in' => $expiresIn,
        ]);
    }

    /**
     * Get JWT.
     * @param User $user
     * @param int $expiresIn
     * @return string
     */
    public function getAccessToken(User $user, int $expiresIn): string
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
        $claims = [
            'iat' => time(),
            'exp' => $expiresIn,
            'sub' => $sub,
        ];
        return JWT::encode($claims, $secret, 'HS256');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getRefreshToken(): string
    {
        return $this->generateRandomString(32);
    }

    /**
     * Generate CSRF token from length.
     * @param int $len
     * @return string
     * @throws Exception
     */
    protected function generateRandomString(int $len): string
    {
        return bin2hex(random_bytes($len));
    }

    /**
     * @param string $token
     * @return LoginResponseResource|null
     * @throws Exception
     */
    public function refresh(string $token): ?LoginResponseResource
    {
        $user = $this->repo->findOne(['refresh_token' => $token]);
        if (!$user instanceof User) {
            return null;
        }

        $response = $this->generateTokens($user);
        $user->refresh_token = $response->refresh_token;
        $this->repo->update($user);

        return $response;
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
        $user = $this->repo->findOne(['id' => $claims['sub']], ['id', 'email', 'name']);
        return $user instanceof User ? $user : null;
    }
}
