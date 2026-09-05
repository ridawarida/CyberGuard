<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $table = 'incidents';

    protected $fillable = [
        'tracking_id',
        'platform',
        'region',
        'description',
        'incident_date',
        'behavior_type',
        'severity',
        'overview',
        'evidence_image',
        'status',
        'assigned_moderator_id',
        'claimed_at',
        'moderator_notes',
        'reviewed_at',
        'ai_risk_score',
        'ai_risk_level',
        'ai_reason',
        'ai_scanned_at',
        'ai_text_risk_score',
        'ai_text_risk_level',
        'ai_text_reason',
        'ai_image_risk_score',
        'ai_image_risk_level',
        'ai_image_reason',
    ];

    protected $casts = [
        'incident_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'claimed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'ai_scanned_at' => 'datetime',
    ];

    // Relationships
    public function caseFileIncidents()
    {
        return $this->hasMany(CaseFileIncident::class, 'incident_tracking_id', 'tracking_id');
    }

    public function caseFiles()
    {
        return $this->belongsToMany(
            CaseFile::class,
            'case_file_incidents',
            'incident_tracking_id',
            'case_file_id',
            'tracking_id',
            'id'
        );
    }

    // Check if incident is already in any case file.
    public function hasCaseFile(): bool
    {
        return $this->caseFileIncidents()->count() > 0;
    }

    // Get the case file this incident belongs to (if any).
    public function getCurrentCaseFile()
    {
        $caseFileIncident = $this->caseFileIncidents()->first();
        return $caseFileIncident ? $caseFileIncident->caseFile : null;
    }

    // Get the tracking ID of the case file this incident belongs to.
    public function getCurrentCaseFileTokenAttribute()
    {
        $caseFile = $this->getCurrentCaseFile();
        return $caseFile ? $caseFile->tracking_id : null;
    }

    // Scope
    public function scopeByTrackingId($query, $trackingId)
    {
        return $query->where('tracking_id', $trackingId);
    }

    /*
    |--------------------------------------------------------------------------
    | Moderation workspace (case ownership + lifecycle)
    |--------------------------------------------------------------------------
    */

    public function assignedModerator()
    {
        return $this->belongsTo(User::class, 'assigned_moderator_id');
    }

    // A case sitting in the open pool has not been locked by anyone yet.
    public function isClaimed(): bool
    {
        return $this->assigned_moderator_id !== null;
    }

    public function isClaimedBy(?User $user): bool
    {
        return $user !== null && $this->assigned_moderator_id === $user->id;
    }

    // Admins can inspect anything, moderators only what they personally claimed.
    public function isReviewableBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->isAdmin() || $this->isClaimedBy($user);
    }

    public function scopeUnclaimed($query)
    {
        return $query->whereNull('assigned_moderator_id');
    }

    public function scopeClaimedBy($query, $userId)
    {
        return $query->where('assigned_moderator_id', $userId);
    }

    /**
     * Applies the moderator dashboard filters: submission date range,
     * platform, region, status and tracking code search.
     */
    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['date_from'] ?? null, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($filters['date_to'] ?? null, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            })
            ->when($filters['platform'] ?? null, function ($q, $platform) {
                $q->where('platform', $platform);
            })
            ->when($filters['region'] ?? null, function ($q, $region) {
                $q->where('region', $region);
            })
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where('tracking_id', 'like', '%' . $term . '%');
            });
    }
}

