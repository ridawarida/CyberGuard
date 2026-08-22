<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseFileIncident extends Model
{
    protected $table = 'case_file_incidents';

    protected $fillable = [
        'case_file_id',
        'incident_tracking_id',
        'incident_overview',
        'incident_date',
        'incident_platform',
        'incident_region',
        'behavior_type',
        'severity',
        'added_at',
    ];

     public function incident()
    {
        return $this->belongsTo(Incident::class, 'incident_tracking_id', 'tracking_id');
    }

    public function caseFile()
    {
        return $this->belongsTo(CaseFile::class, 'case_file_id', 'id');
    }
}
