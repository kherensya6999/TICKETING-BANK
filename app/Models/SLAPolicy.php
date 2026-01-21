<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SLAPolicy extends Model
{
    protected $table = 'sla_policies';

    protected $fillable = [
        'policy_name',
        'priority',
        'first_response_target',
        'resolution_target',
        'business_hours_only',
        'business_hours_start',
        'business_hours_end',
        'business_days',
        'is_active',
    ];

    protected $casts = [
        'business_hours_only' => 'boolean',
        'business_days' => 'array',
        'is_active' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'sla_id');
    }

    public function slaTrackings(): HasMany
    {
        return $this->hasMany(TicketSLATracking::class, 'sla_policy_id');
    }
}
