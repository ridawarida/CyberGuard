<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Timeline extends Model
{
    protected $table = 'timelines';

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

        static::creating(function ($timeline) {
            if (empty($timeline->tracking_id)) {
                $timeline->tracking_id = self::generateTrackingId();
            }
        });
    }

    /**
     * Generate a unique tracking ID for the timeline.
     */
    public static function generateTrackingId(): string
    {
        do {
            $trackingId = 'tl' . Str::random(12);
        } while (self::where('tracking_id', $trackingId)->exists());

        return $trackingId;
    }

    /**
     * Get the timeline reports associated with this timeline.
     */
    public function timelineReports()
    {
        return $this->hasMany(TimelineReport::class, 'timeline_id', 'id');
    }

    /**
     * Get all incidents linked through timeline_reports.
     */
    public function incidents()
    {
        return $this->belongsToMany(
            Incident::class,
            'timeline_reports',
            'timeline_id',
            'report_tracking_id',
            'id',
            'tracking_id'
        );
    }

    /**
     * Scope a query to only include timelines by tracking_id.
     */
    public function scopeByTrackingId($query, $trackingId)
    {
        return $query->where('tracking_id', $trackingId);
    }

    /**
     * Scope a query to only include timelines by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the earliest incident date in this timeline.
     */
    public function getEarliestIncidentDateAttribute()
    {
        $report = $this->timelineReports()
            ->orderBy('report_incident_date', 'asc')
            ->first();

        return $report ? $report->report_incident_date : null;
    }

    /**
     * Get the latest incident date in this timeline.
     */
    public function getLatestIncidentDateAttribute()
    {
        $report = $this->timelineReports()
            ->orderBy('report_incident_date', 'desc')
            ->first();

        return $report ? $report->report_incident_date : null;
    }

    /**
     * Get the date range string for this timeline.
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
     * Check if the timeline has any incidents.
     */
    public function hasIncidents(): bool
    {
        return $this->timelineReports()->count() > 0;
    }

    /**
     * Get the incident count for this timeline.
     */
    public function getIncidentCountAttribute()
    {
        return $this->timelineReports()->count();
    }

    /**
     * Add an incident to this timeline.
     */
    public function addIncident(Incident $incident): TimelineReport
    {
        // Check if incident already belongs to ANY timeline
        if ($incident->hasTimeline()) {
            $currentTimeline = $incident->getCurrentTimeline();
            $currentToken = $currentTimeline ? $currentTimeline->tracking_id : 'unknown';
            
            throw new \Exception("Incident '{$incident->tracking_id}' already belongs to timeline '{$currentToken}'.");
        }

        return $this->timelineReports()->create([
            'report_tracking_id' => $incident->tracking_id,
            'report_overview' => $incident->overview ?? $incident->description,
            'report_incident_date' => $incident->incident_date,
            'report_platform' => $incident->platform,
            'report_region' => $incident->region,
            'behavior_type' => $incident->behavior_type,
            'severity' => $incident->severity ?? null,
            'added_at' => now(),
        ]);
    }

    public function canAddIncident(Incident $incident): bool
    {
        // If incident already has a timeline, it cannot be added
        if ($incident->hasTimeline()) {
            return false;
        }
        return true;
    }

    /**
     * Remove an incident from this timeline.
     */
    public function removeIncident(string $incidentTrackingId): bool
    {
        return $this->timelineReports()
            ->where('report_tracking_id', $incidentTrackingId)
            ->delete() > 0;
    }

    /**
     * Check if an incident belongs to this timeline.
     */
    public function hasIncident(string $incidentTrackingId): bool
    {
        return $this->timelineReports()
            ->where('report_tracking_id', $incidentTrackingId)
            ->exists();
    }

    /**
     * Get all incident tracking IDs in this timeline.
     */
    public function getIncidentTrackingIdsAttribute()
    {
        return $this->timelineReports()
            ->pluck('report_tracking_id')
            ->toArray();
    }

    /**
     * Get a formatted summary of the timeline.
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
