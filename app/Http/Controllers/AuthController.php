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

            $user = User::where('username', $request->username)
                ->orWhere('email', $request->username)
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                $user?->increment('failed_login_attempts');
                
                if ($user && $user->failed_login_attempts >= 5) {
                    $user->update(['locked_until' => Carbon::now()->addHours(1)]);
                }

                AuditLog::create([
                    'action_type' => 'LOGIN_FAILED',
                    'description' => "Failed login attempt for username: {$request->username}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is deactivated'
                ], 403);
            }

            if ($user->locked_until && $user->locked_until->isFuture()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is locked. Please try again later.'
                ], 423);
            }

            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now(),
            ]);

            $token = $this->authService->generateToken($user);

            UserSession::create([
                'user_id' => $user->id,
                'session_token' => $token,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expires_at' => Carbon::now()->addDays(7),
                'last_activity_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action_type' => 'LOGIN',
                'description' => "User logged in",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'employee_id' => $user->employee_id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'full_name' => $user->full_name,
                        'role' => $user->role->role_name,
                        'permissions' => $user->role->permissions ?? [],
                        'department' => $user->department?->department_name,
                        'branch' => $user->branch?->branch_name,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            
            if ($token) {
                UserSession::where('session_token', $token)
                    ->update(['is_active' => false]);
            }

            $user = $request->user();
            if ($user) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action_type' => 'LOGOUT',
                    'description' => "User logged out",
                    'ip_address' => $request->ip(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|string|max:20|unique:users,employee_id',
                'username' => 'required|string|max:50|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
                'email' => 'required|email|max:100|unique:users,email',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[a-z]/',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[@$!%*#?&]/',
                    'confirmed',
                ],
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'phone' => 'nullable|string|max:20|regex:/^[0-9+\-() ]+$/',
                'role' => 'required|string|in:USER,ADMIN',
                'admin_code' => 'required_if:role,ADMIN|string',
                'department_id' => 'nullable|exists:departments,id',
                'branch_id' => 'nullable|exists:branches,id',
            ], [
                'password.regex' => 'Password must contain at least one uppercase, one lowercase, one number, and one special character.',
                'password.min' => 'Password must be at least 8 characters long.',
                'username.regex' => 'Username can only contain letters, numbers, and underscores.',
                'admin_code.required_if' => 'Admin registration code is required for admin accounts.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify admin code if registering as admin
            if ($request->role === 'ADMIN') {
                $adminCode = env('ADMIN_REGISTRATION_CODE', 'BANKSUMUT2026ADMIN');
                if ($request->admin_code !== $adminCode) {
                    AuditLog::create([
                        'action_type' => 'REGISTER_FAILED',
                        'description' => "Failed admin registration attempt with invalid code",
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid admin registration code'
                    ], 403);
                }
            }

            // Get role ID
            $role = \App\Models\Role::where('role_name', $request->role)->first();
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid role specified'
                ], 400);
            }

            // Create user
            $user = User::create([
                'employee_id' => $request->employee_id,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'role_id' => $role->id,
                'department_id' => $request->department_id,
                'branch_id' => $request->branch_id,
                'is_active' => $request->role === 'ADMIN' ? true : false, // Admin auto-active, User needs approval
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action_type' => 'REGISTER',
                'description' => "User registered with role: {$request->role}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => $request->role === 'ADMIN' 
                    ? 'Admin account created successfully' 
                    : 'Registration successful. Your account is pending approval.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'employee_id' => $user->employee_id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'full_name' => $user->full_name,
                        'role' => $role->role_name,
                        'is_active' => $user->is_active,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'employee_id' => $user->employee_id,
                'username' => $user->username,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'role' => $user->role->role_name,
                'permissions' => $user->role->permissions ?? [],
                'department' => $user->department?->department_name,
                'branch' => $user->branch?->branch_name,
            ]
        ]);
    }
}
