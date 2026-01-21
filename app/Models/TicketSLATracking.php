<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSLATracking extends Model
{
    protected $table = 'ticket_sla_tracking';

    protected $fillable = [
        'ticket_id',
        'sla_policy_id',
        'first_response_target_at',
        'first_response_actual_at',
        'first_response_breached',
        'resolution_target_at',
        'resolution_actual_at',
        'resolution_breached',
        'overall_sla_status',
    ];

    protected $casts = [
        'first_response_target_at' => 'datetime',
        'first_response_actual_at' => 'datetime',
        'first_response_breached' => 'boolean',
        'resolution_target_at' => 'datetime',
        'resolution_actual_at' => 'datetime',
        'resolution_breached' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SLAPolicy::class, 'sla_policy_id');
    }
}
