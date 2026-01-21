<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketCategory extends Model
{
    protected $fillable = [
        'category_name',
        'category_code',
        'default_priority',
        'default_sla_id',
        'default_team_id',
        'is_security_related',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_security_related' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function defaultSLA(): BelongsTo
    {
        return $this->belongsTo(SLAPolicy::class, 'default_sla_id');
    }

    public function defaultTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'default_team_id');
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(TicketSubcategory::class, 'category_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }
}
