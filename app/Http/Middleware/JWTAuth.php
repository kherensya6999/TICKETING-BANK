<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Models\User;
use App\Models\UserSession;
use Symfony\Component\HttpFoundation\Response;

class JWTAuth
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            \Illuminate\Support\Facades\Log::warning('JWTAuth: Token not provided');
            return response()->json([
                'success' => false,
                'message' => 'Token not provided'
            ], 401);
        }

        $payload = $this->authService->validateToken($token);

        if (!$payload) {
            \Illuminate\Support\Facades\Log::warning('JWTAuth: Invalid or expired token', ['token' => substr($token, 0, 10) . '...']);
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 401);
        }

        $session = UserSession::where('session_token', $token)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();

        if (!$session) {
            \Illuminate\Support\Facades\Log::warning('JWTAuth: Session not found in DB', [
                'token_preview' => substr($token, 0, 10) . '...',
                'user_id' => $payload->sub ?? 'unknown'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Session expired'
            ], 401);
        }

        $session->update(['last_activity_at' => now()]);

        // Fix: Access user ID from 'sub' claim (standard JWT) or 'user' object
        // The previous code $payload['user_id'] was incorrect because:
        // 1. JWT::decode returns an object, not an array
        // 2. The key 'user_id' does not exist in AuthService payload (it uses 'sub' and 'user.id')
        $userId = $payload->sub ?? ($payload->user->id ?? null);

        if (!$userId) {
             \Illuminate\Support\Facades\Log::warning('JWTAuth: User ID not found in token payload');
             return response()->json([
                 'success' => false,
                 'message' => 'Invalid token structure'
             ], 401);
        }

        $user = User::find($userId);

        if (!$user || !$user->is_active) {
            \Illuminate\Support\Facades\Log::warning('JWTAuth: User not found or inactive', ['user_id' => $userId]);
            return response()->json([
                'success' => false,
                'message' => 'User not found or inactive'
            ], 401);
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
