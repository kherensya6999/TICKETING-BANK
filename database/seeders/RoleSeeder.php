<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'role_name' => 'Admin',
                'role_code' => 'ADMIN',
                'permissions' => ['*'],
                'description' => 'System Administrator',
            ],
            [
                'role_name' => 'Security Team Lead',
                'role_code' => 'SEC_LEAD',
                'permissions' => [
                    'TICKET_VIEW_ALL',
                    'TICKET_MODIFY_ALL',
                    'TICKET_ASSIGN',
                    'SECURITY_INCIDENT_MANAGE',
                    'REPORT_VIEW',
                ],
            ],
            [
                'role_name' => 'Security Analyst',
                'role_code' => 'ANALYST',
                'permissions' => [
                    'TICKET_VIEW_ASSIGNED',
                    'TICKET_MODIFY_ASSIGNED',
                    'TICKET_RESOLVE',
                    'COMMENT_ADD',
                ],
            ],
            [
                'role_name' => 'End User',
                'role_code' => 'USER',
                'permissions' => [
                    'TICKET_CREATE',
                    'TICKET_VIEW_OWN',
                    'COMMENT_ADD',
                ],
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
