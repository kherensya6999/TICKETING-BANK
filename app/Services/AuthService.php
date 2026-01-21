<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    private $secretKey;

    public function __construct()
    {
        $this->secretKey = config('app.key');
    }

    public function generateToken(User $user): string
    {
        $payload = [
            'iss' => config('app.url'),
            'iat' => now()->timestamp,
            'exp' => now()->addDays(7)->timestamp,
            'user_id' => $user->id,
            'employee_id' => $user->employee_id,
            'role_id' => $user->role_id,
            'permissions' => $user->role->permissions ?? [],
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }
}
