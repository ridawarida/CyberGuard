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
}
