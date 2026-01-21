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

            // SECURITY: Generic Error Message to prevent Username Enumeration
            if (!$user || !Hash::check($request->password, $user->password)) {
                $user?->increment('failed_login_attempts');
                
                if ($user && $user->failed_login_attempts >= 5) {
                    $user->update(['locked_until' => Carbon::now()->addHours(1)]);
                    
                    AuditLog::create([
                        'user_id' => $user->id,
                        'action_type' => 'ACCOUNT_LOCKED',
                        'description' => "Account locked due to multiple failed attempts",
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // SECURITY: Check Active Status (Pending Approval)
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is pending approval or deactivated. Please contact support.'
                ], 403);
            }

            if ($user->locked_until && $user->locked_until->isFuture()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is locked. Please try again later.'
                ], 423);
            }

            // SECURITY: Re-hash password if needed (future proofing)
            if (Hash::needsRehash($user->password)) {
                $user->password = Hash::make($request->password);
            }

            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now(),
            ]);

            $token = $this->authService->generateToken($user);

            // SECURITY: Strict Session Logging
            UserSession::create([
                'user_id' => $user->id,
                'session_token' => $token, // Consider hashing this if storing in DB for strict security
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expires_at' => Carbon::now()->addDays(1), // Reduced from 7 days for better security
                'last_activity_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action_type' => 'LOGIN',
                'description' => "User logged in successfully",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'full_name' => $user->full_name,
                        'role' => $user->role->role_name,
                        'permissions' => $user->role->permissions ?? [],
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

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|string|max:20|unique:users,employee_id',
                'username' => 'required|string|max:50|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
                'email' => 'required|email|max:100|unique:users,email',
                'password' => [
                    'required', 'string', 'min:8',
                    'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/',
                    'confirmed',
                ],
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'role' => 'required|string|in:USER,ADMIN',
                // Frontend sends 'admin_code', but backend treats it as Invitation Token now
                'admin_code' => 'required_if:role,ADMIN|string', 
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
            }

            $isActive = false; // Default: Inactive until approved
            $invitation = null;

            // SECURITY: Invitation System for ADMIN
            if ($request->role === 'ADMIN') {
                // Find valid invitation token (replaces hardcoded check)
                $invitation = AdminInvitation::where('token', $request->admin_code) // Frontend field 'admin_code' is reused as token
                    ->where('email', $request->email)
                    ->where('expires_at', '>', now())
                    ->whereNull('registered_at')
                    ->first();

                if (!$invitation) {
                    AuditLog::create([
                        'action_type' => 'REGISTER_FAILED',
                        'description' => "Invalid or expired admin invitation token for email: {$request->email}",
                        'ip_address' => $request->ip(),
                    ]);
                    return response()->json(['success' => false, 'message' => 'Invalid or expired invitation token.'], 403);
                }

                // If valid invitation, auto-activate
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
                'is_active' => $isActive, // Users = False, Invited Admins = True
            ]);

            // Mark invitation as used
            if ($invitation) {
                $invitation->update(['registered_at' => now()]);
            }

            AuditLog::create([
                'user_id' => $user->id,
                'action_type' => 'REGISTER',
                'description' => "User registered as {$request->role}. Status: " . ($isActive ? 'Active' : 'Pending Approval'),
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isActive 
                    ? 'Registration successful. You can now login.' 
                    : 'Registration successful. Please wait for admin approval.',
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
            // Audit log logic remains...
            return response()->json(['success' => true, 'message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Logout failed'], 500);
        }
    }
    
    public function me(Request $request)
    {
        return response()->json(['success' => true, 'data' => $request->user()->load('role', 'department', 'branch')]);
    }
}