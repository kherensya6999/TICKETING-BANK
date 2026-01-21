<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ThreatIntelligence extends Model
{
    protected $fillable = [
        'threat_name',
        'threat_type',
        'description',
        'indicators_of_compromise',
        'mitre_attack_techniques',
        'severity',
        'source',
        'first_seen_at',
        'last_seen_at',
        'is_active',
    ];

    protected $casts = [
        'indicators_of_compromise' => 'array',
        'mitre_attack_techniques' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function incidents(): BelongsToMany
    {
        return $this->belongsToMany(SecurityIncident::class, 'incident_threat_mapping')
            ->withPivot('confidence_score', 'notes')
            ->withTimestamps();
    }
}
