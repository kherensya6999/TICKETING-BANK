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
        // Mengambil key dari .env, jika kosong pakai default
        $this->secretKey = env('JWT_SECRET', 'rahasia_bank_sumut_2026_secure_key_wajib_ganti');
    }

    public function generateToken(User $user): string
    {
        $payload = [
            'iss' => env('APP_URL', 'http://localhost:8000'), // Issuer
            'iat' => now()->timestamp, // Issued At
            'exp' => now()->addDays(1)->timestamp, // Expire 1 hari
            'sub' => $user->id, // Subject (User ID)
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role->role_name ?? 'USER',
            ]
        ];

        // Generate Token menggunakan algoritma HS256
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function validateToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->secretKey, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }
}