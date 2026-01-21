<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AdminInvitation;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Get List Users (Pagination + Search + Filter)
    public function getUsers(Request $request)
    {
        // Jangan tampilkan diri sendiri agar tidak tidak sengaja mengubah status sendiri
        $query = User::with(['role', 'department', 'branch'])
            ->where('id', '!=', $request->user()->id); 

        // Filter by Status
        if ($request->has('status') && $request->status !== '') {
            $is_active = $request->status === 'active' ? 1 : 0;
            $query->where('is_active', $is_active);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%") // Pastikan kolom ini ada di DB atau ganti first_name
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json(['success' => true, 'data' => $users]);
    }

    // Approve User Baru (Pending -> Active)
    public function approveUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->is_active) {
            return response()->json(['success' => false, 'message' => 'User is already active'], 400);
        }

        $user->update(['is_active' => true]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action_type' => 'USER_APPROVED',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'description' => "User {$user->username} approved by " . $request->user()->username,
            'ip_address' => $request->ip()
        ]);

        return response()->json(['success' => true, 'message' => 'User approved successfully']);
    }

    // Buat Undangan Admin Baru (Hanya Super Admin)
    public function inviteAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email|unique:admin_invitations,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $token = Str::random(32); // Generate Random Token

        $invitation = AdminInvitation::create([
            'email' => $request->email,
            'token' => $token,
            'created_by' => $request->user()->id,
            'expires_at' => now()->addHours(24), // Token valid 24 jam
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action_type' => 'ADMIN_INVITED',
            'description' => "Invited admin email: {$request->email}",
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Invitation generated successfully',
            'data' => [
                'email' => $invitation->email,
                'token' => $token, 
                'expires_in' => '24 hours'
            ]
        ]);
    }
}