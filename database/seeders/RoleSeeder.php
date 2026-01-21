<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan tabel bersih dulu atau gunakan updateOrCreate
        
        $roles = [
            [
                'role_name' => 'SUPER_ADMIN',
                'role_code' => 'SA',
                'description' => 'Highest level access, can manage admins and settings',
                'permissions' => [
                    'ALL_ACCESS',
                    'MANAGE_ADMINS',
                    'APPROVE_USERS',
                    'VIEW_AUDIT_LOGS'
                ]
            ],
            [
                'role_name' => 'ADMIN',
                'role_code' => 'ADM',
                'description' => 'Administrator access',
                'permissions' => [
                    'TICKET_MANAGE_ALL',
                    'USER_READ',
                    'REPORT_VIEW'
                ]
            ],
            [
                'role_name' => 'USER',
                'role_code' => 'USR',
                'description' => 'Standard user access',
                'permissions' => [
                    'TICKET_CREATE',
                    'TICKET_READ_OWN',
                    'TICKET_UPDATE_OWN'
                ]
            ],
            [
                'role_name' => 'TECHNICIAN',
                'role_code' => 'TECH',
                'description' => 'IT Support Staff',
                'permissions' => [
                    'TICKET_READ_ASSIGNED',
                    'TICKET_UPDATE_ASSIGNED',
                    'TICKET_RESOLVE'
                ]
            ]
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['role_name' => $role['role_name']],
                [
                    'role_code' => $role['role_code'],
                    'description' => $role['description'],
                    'permissions' => $role['permissions'],
                    'is_active' => true
                ]
            );
        }
    }
}