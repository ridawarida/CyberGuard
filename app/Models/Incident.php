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
    ];

    protected $casts = [
        'incident_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function timelineReports()
    {
        return $this->hasMany(TimelineReport::class, 'report_tracking_id', 'tracking_id');
    }

    public function timelines()
    {
        return $this->belongsToMany(
            Timeline::class,
            'timeline_reports',
            'report_tracking_id',
            'timeline_id',
            'tracking_id',
            'id'
        );
    }

    //Check if incident is already in ANY timeline
    public function hasTimeline(): bool
    {
        return $this->timelineReports()->count() > 0;
    }

    //Get the timeline this incident belongs to (if any)
    public function getCurrentTimeline()
    {
        $timelineReport = $this->timelineReports()->first();
        return $timelineReport ? $timelineReport->timeline : null;
    }

    //Get the tracking ID of the timeline this incident belongs to
    public function getCurrentTimelineTokenAttribute()
    {
        $timeline = $this->getCurrentTimeline();
        return $timeline ? $timeline->tracking_id : null;
    }

    // Scope
    public function scopeByTrackingId($query, $trackingId)
    {
        return $query->where('tracking_id', $trackingId);
    }
}

