<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'ticket_type',
        'category_id',
        'subcategory_id',
        'requester_id',
        'assigned_to_id',
        'team_id',
        'status',
        'priority',
        'sla_id',
        'due_date',
        'subject',
        'description',
        'is_security_incident',
        'is_sla_breached',
        'is_escalated',
        'escalation_level',
        'first_response_at',
        'resolved_at',
        'resolution_duration',
        'resolution_status',
        'resolution_summary',
        'root_cause',
        'actions_taken',
        'preventive_measures',
        'satisfaction_rating',
        'satisfaction_feedback',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_security_incident' => 'boolean',
        'is_sla_breached' => 'boolean',
        'is_escalated' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(TicketSubcategory::class, 'subcategory_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function sla(): BelongsTo
    {
        return $this->belongsTo(SLAPolicy::class, 'sla_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TicketHistory::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_watchers')
            ->withPivot('notify_on_update', 'notify_on_comment', 'notify_on_status_change')
            ->withTimestamps();
    }

    public function slaTracking(): HasOne
    {
        return $this->hasOne(TicketSLATracking::class);
    }

    public function escalations(): HasMany
    {
        return $this->hasMany(TicketEscalation::class);
    }

    public function securityIncident(): HasOne
    {
        return $this->hasOne(SecurityIncident::class);
    }

    // Scopes
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeSecurityIncident(Builder $query): Builder
    {
        return $query->where('is_security_incident', true);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to_id', $userId);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['RESOLVED', 'CLOSED', 'CANCELLED']);
    }
}
