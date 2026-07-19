<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimelineReport extends Model
{
    protected $table = 'timeline_reports';

    protected $fillable = [
        'timeline_id',
        'report_tracking_id',
        'report_overview',
        'report_incident_date',
        'report_platform',
        'report_region',
        'behavior_type',
        'severity',
        'added_at',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class, 'report_tracking_id', 'tracking_id');
    }
}
