<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil role SUPER_ADMIN yang sudah dibuat RoleSeeder
        $superAdminRole = Role::where('role_code', 'SA')->first();

        if ($superAdminRole) {
            User::updateOrCreate(
                ['email' => 'superadmin@banksumut.co.id'], // Email unik
                [
                    'employee_id' => 'SA001',
                    'username' => 'superadmin',
                    'first_name' => 'Super',
                    'last_name' => 'Admin',
                    // Password Default: BankSumut@2026 (Ganti setelah login!)
                    'password' => Hash::make('BankSumut@2026'), 
                    'phone' => '081234567890',
                    'role_id' => $superAdminRole->id,
                    'is_active' => true, // Langsung aktif
                ]
            );
        }
    }
}