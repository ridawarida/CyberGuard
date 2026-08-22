<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class CaseFile extends Model
{
    protected $table = 'case_files';

    protected $fillable = [
        'id',
        'tracking_id',
        'description',
        'category',
        'created_at',
        'updated_at'
    ];
 protected static function boot()
    {
        parent::boot();

        static::creating(function ($caseFile) {
            if (empty($caseFile->tracking_id)) {
                $caseFile->tracking_id = self::generateTrackingId();
            }
        });
    }

    /**
    * Generate a unique tracking ID for the case file.
     */
    public static function generateTrackingId(): string
    {
        do {
            $trackingId = 'cf' . Str::random(12);
        } while (self::where('tracking_id', $trackingId)->exists());

        return $trackingId;
    }

    /**
    * Get the incident entries associated with this case file.
     */
    public function caseFileIncidents()
    {
        return $this->hasMany(CaseFileIncident::class, 'case_file_id', 'id');
    }

    /**
    * Get all incidents linked through case_file_incidents.
     */
    public function incidents()
    {
        return $this->belongsToMany(
            Incident::class,
            'case_file_incidents',
            'case_file_id',
            'incident_tracking_id',
            'id',
            'tracking_id'
        );
    }

    /**
    * Scope a query to only include case files by tracking_id.
     */
    public function scopeByTrackingId($query, $trackingId)
    {
        return $query->where('tracking_id', $trackingId);
    }

    /**
    * Scope a query to only include case files by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
    * Get the earliest incident date in this case file.
     */
    public function getEarliestIncidentDateAttribute()
    {
        $incident = $this->caseFileIncidents()
            ->orderBy('incident_date', 'asc')
            ->first();

        return $incident ? $incident->incident_date : null;
    }

    /**
    * Get the latest incident date in this case file.
     */
    public function getLatestIncidentDateAttribute()
    {
        $incident = $this->caseFileIncidents()
            ->orderBy('incident_date', 'desc')
            ->first();

        return $incident ? $incident->incident_date : null;
    }

    /**
    * Get the date range string for this case file.
     */
    public function getDateRangeAttribute()
    {
        $earliest = $this->earliest_incident_date;
        $latest = $this->latest_incident_date;

        if (!$earliest || !$latest) {
            return null;
        }

        if ($earliest === $latest) {
            return $earliest->format('M d, Y');
        }

        return $earliest->format('M d, Y') . ' - ' . $latest->format('M d, Y');
    }

    /**
    * Check if the case file has any incidents.
     */
    public function hasIncidents(): bool
    {
        return $this->caseFileIncidents()->count() > 0;
    }

    /**
    * Get the incident count for this case file.
     */
    public function getIncidentCountAttribute()
    {
        return $this->caseFileIncidents()->count();
    }

    /**
    * Add an incident to this case file.
     */
    public function addIncident(Incident $incident): CaseFileIncident
    {
        // Check if incident already belongs to any case file.
        if ($incident->hasCaseFile()) {
            $currentCaseFile = $incident->getCurrentCaseFile();
            $currentToken = $currentCaseFile ? $currentCaseFile->tracking_id : 'unknown';
            
            throw new \Exception("Incident '{$incident->tracking_id}' already belongs to case file '{$currentToken}'.");
        }

        return $this->caseFileIncidents()->create([
            'incident_tracking_id' => $incident->tracking_id,
            'incident_overview' => $incident->overview ?? $incident->description,
            'incident_date' => $incident->incident_date,
            'incident_platform' => $incident->platform,
            'incident_region' => $incident->region,
            'behavior_type' => $incident->behavior_type,
            'severity' => $incident->severity ?? null,
            'added_at' => now(),
        ]);
    }

    public function canAddIncident(Incident $incident): bool
    {
        // If incident already has a case file, it cannot be added
        if ($incident->hasCaseFile()) {
            return false;
        }
        return true;
    }

    /**
    * Remove an incident from this case file.
     */
    public function removeIncident(string $incidentTrackingId): bool
    {
        return $this->caseFileIncidents()
            ->where('incident_tracking_id', $incidentTrackingId)
            ->delete() > 0;
    }

    /**
    * Check if an incident belongs to this case file.
     */
    public function hasIncident(string $incidentTrackingId): bool
    {
        return $this->caseFileIncidents()
            ->where('incident_tracking_id', $incidentTrackingId)
            ->exists();
    }

    /**
    * Get all incident tracking IDs in this case file.
     */
    public function getIncidentTrackingIdsAttribute()
    {
        return $this->caseFileIncidents()
            ->pluck('incident_tracking_id')
            ->toArray();
    }

    /**
    * Get a formatted summary of the case file.
     */
    public function getSummaryAttribute(): array
    {
        return [
            'tracking_id' => $this->tracking_id,
            'description' => $this->description,
            'category' => $this->category,
            'incident_count' => $this->incident_count,
            'date_range' => $this->date_range,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
