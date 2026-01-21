<?php

namespace Database\Seeders;

use App\Models\SLAPolicy;
use Illuminate\Database\Seeder;

class SLAPolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            [
                'policy_name' => 'Critical Priority SLA',
                'priority' => 'CRITICAL',
                'first_response_target' => 30,
                'resolution_target' => 240,
                'business_hours_only' => false,
            ],
            [
                'policy_name' => 'Urgent Priority SLA',
                'priority' => 'URGENT',
                'first_response_target' => 60,
                'resolution_target' => 480,
                'business_hours_only' => false,
            ],
            [
                'policy_name' => 'High Priority SLA',
                'priority' => 'HIGH',
                'first_response_target' => 120,
                'resolution_target' => 1440,
                'business_hours_only' => true,
                'business_hours_start' => '09:00',
                'business_hours_end' => '17:00',
                'business_days' => [1, 2, 3, 4, 5],
            ],
            [
                'policy_name' => 'Medium Priority SLA',
                'priority' => 'MEDIUM',
                'first_response_target' => 240,
                'resolution_target' => 2880,
                'business_hours_only' => true,
                'business_hours_start' => '09:00',
                'business_hours_end' => '17:00',
                'business_days' => [1, 2, 3, 4, 5],
            ],
            [
                'policy_name' => 'Low Priority SLA',
                'priority' => 'LOW',
                'first_response_target' => 480,
                'resolution_target' => 7200,
                'business_hours_only' => true,
                'business_hours_start' => '09:00',
                'business_hours_end' => '17:00',
                'business_days' => [1, 2, 3, 4, 5],
            ],
        ];

        foreach ($policies as $policy) {
            SLAPolicy::create($policy);
        }
    }
}
