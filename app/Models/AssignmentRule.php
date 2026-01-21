<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentRule extends Model
{
    protected $fillable = [
        'rule_name',
        'rule_type',
        'conditions',
        'assign_to_team_id',
        'assign_to_user_id',
        'priority_order',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public function assignToTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'assign_to_team_id');
    }

    public function assignToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assign_to_user_id');
    }
}
