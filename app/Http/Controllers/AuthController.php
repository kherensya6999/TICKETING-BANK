<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSession;
use App\Models\AuditLog;
use App\Models\AdminInvitation;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Pastikan Log diimport

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

            // Cari user
            $user = User::where('username', $request->username)
                ->orWhere('email', $request->username)
                ->first();

            // Cek apakah user ditemukan
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 401);
            }

            // Cek password manual
            if (!Hash::check($request->password, $user->password)) {
                $user->increment('failed_login_attempts');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password'
                ], 401);
            }

            // Cek status aktif
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is pending approval or deactivated.'
                ], 403);
            }

            // Reset login attempts
            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now(),
            ]);

            // Generate token
            $token = $this->authService->generateToken($user);

            // Buat session
            UserSession::create([
                'user_id' => $user->id,
                'session_token' => $token,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expires_at' => Carbon::now()->addDays(1),
                'last_activity_at' => now(),
            ]);

            // Format data response yang rapi
            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'employee_id' => $user->employee_id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'full_name' => $user->first_name . ' ' . $user->last_name, // Manual concat
                        'role' => $user->role->role_name ?? 'USER',
                        'permissions' => $user->role->permissions ?? [],
                        // Gunakan optional helper untuk menghindari error pada relasi null
                        'department' => optional($user->department)->department_name,
                        'branch' => optional($user->branch)->branch_name,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            // Log error ke laravel.log
            Log::error('Login Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Login failed (Server Error): ' . $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|string|max:20|unique:users,employee_id',
                'username' => 'required|string|max:50|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
                'email' => 'required|email|max:100|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'role' => 'required|string|in:USER,ADMIN',
                'admin_code' => 'required_if:role,ADMIN|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
            }

            $isActive = false;
            $invitation = null;

            if ($request->role === 'ADMIN') {
                $invitation = AdminInvitation::where('token', $request->admin_code)
                    ->where('email', $request->email)
                    ->where('expires_at', '>', now())
                    ->whereNull('registered_at')
                    ->first();

                if (!$invitation) {
                    return response()->json(['success' => false, 'message' => 'Invalid/expired invitation token.'], 403);
                }
                $isActive = true;
            }

            $role = \App\Models\Role::where('role_name', $request->role)->firstOrFail();

            $user = User::create([
                'employee_id' => $request->employee_id,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone ?? null,
                'role_id' => $role->id,
                'department_id' => $request->department_id ?? null,
                'branch_id' => $request->branch_id ?? null,
                'is_active' => $isActive,
            ]);

            if ($invitation) {
                $invitation->update(['registered_at' => now()]);
            }

            AuditLog::create([
                'user_id' => $user->id,
                'action_type' => 'REGISTER',
                'description' => "User registered as {$request->role}",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isActive ? 'Registration successful.' : 'Registration successful. Waiting for approval.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if ($token) {
                UserSession::where('session_token', $token)->update(['is_active' => false]);
            }
            return response()->json(['success' => true, 'message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Logout failed'], 500);
        }
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('role', 'department', 'branch');
        
        return response()->json([
            'success' => true, 
            'data' => [
                'id' => $user->id,
                'employee_id' => $user->employee_id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->first_name . ' ' . $user->last_name,
                'role' => $user->role->role_name,
                'permissions' => $user->role->permissions ?? [],
                'department' => optional($user->department)->department_name,
                'branch' => optional($user->branch)->branch_name,
            ]
        ]);
    }
}