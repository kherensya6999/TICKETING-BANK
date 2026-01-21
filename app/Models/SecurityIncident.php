<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SecurityIncident extends Model
{
    protected $fillable = [
        'ticket_id',
        'incident_number',
        'incident_classification',
        'attack_vector',
        'confidentiality_impact',
        'integrity_impact',
        'availability_impact',
        'investigation_status',
        'detected_at',
        'contained_at',
        'eradicated_at',
        'recovered_at',
        'forensic_evidence_collected',
        'evidence_storage_location',
        'detection_method',
        'affected_assets',
        'root_cause_category',
        'root_cause_description',
        'immediate_actions_taken',
        'remediation_actions',
        'preventive_measures',
        'requires_regulatory_reporting',
        'regulatory_bodies_notified',
        'customers_notified_at',
        'lessons_learned',
        'post_incident_review_completed',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'contained_at' => 'datetime',
        'eradicated_at' => 'datetime',
        'recovered_at' => 'datetime',
        'customers_notified_at' => 'datetime',
        'forensic_evidence_collected' => 'boolean',
        'requires_regulatory_reporting' => 'boolean',
        'post_incident_review_completed' => 'boolean',
        'affected_assets' => 'array',
        'regulatory_bodies_notified' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function threats(): BelongsToMany
    {
        return $this->belongsToMany(ThreatIntelligence::class, 'incident_threat_mapping')
            ->withPivot('confidence_score', 'notes')
            ->withTimestamps();
    }
}
