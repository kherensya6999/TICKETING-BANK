<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSession;
use App\Models\AuditLog;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // PENTING

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        try {
            // 1. Log Input (Jangan log password!)
            Log::info('Login Attempt:', ['username' => $request->username]);

            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 2. Cari User
            $user = User::where('username', $request->username)
                ->orWhere('email', $request->username)
                ->first();

            if (!$user) {
                Log::warning('Login Failed: User not found', ['input' => $request->username]);
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 401);
            }

            // 3. Cek Password
            if (!Hash::check($request->password, $user->password)) {
                Log::warning('Login Failed: Wrong Password', ['user_id' => $user->id]);
                $user->increment('failed_login_attempts');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials (Password Wrong)'
                ], 401);
            }

            // 4. Cek Status Aktif
            if (!$user->is_active) {
                Log::warning('Login Failed: User Inactive', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Account is inactive or pending approval.'
                ], 403);
            }

            // 5. Sukses Login
            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now(),
            ]);

            $token = $this->authService->generateToken($user);

            // Log Session (Opsional, pastikan tabel user_sessions ada dan kolomnya sesuai)
            try {
                UserSession::create([
                    'user_id' => $user->id,
                    'session_token' => $token,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'expires_at' => Carbon::now()->addDays(1),
                    'last_activity_at' => now(),
                ]);
            } catch (\Exception $sessionError) {
                Log::error('Session Create Failed (Non-Fatal): ' . $sessionError->getMessage());
                // Lanjut saja meski gagal catat session DB
            }

            Log::info('Login Success:', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'employee_id' => $user->employee_id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'full_name' => $user->first_name . ' ' . $user->last_name,
                        'role' => $user->role->role_name ?? 'USER',
                        'permissions' => $user->role->permissions ?? [],
                        'department' => optional($user->department)->department_name,
                        'branch' => optional($user->branch)->branch_name,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Login Critical Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ... (Fungsi register, logout, me biarkan seperti yang sudah saya berikan sebelumnya)
    public function register(Request $request) { /* Gunakan kode sebelumnya */ }
    public function logout(Request $request) { /* Gunakan kode sebelumnya */ }
    public function me(Request $request) { /* Gunakan kode sebelumnya */ }
}