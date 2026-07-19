<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'address',
        'city',
        'state',
        'zip_code',
        'latitude',
        'longitude',
        'working_hours',
        'is_active',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function hotlines()
    {
        return $this->hasMany(Hotline::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'hospital' => 'Hospital',
            'police_station' => 'Police Station',
            'clinic' => 'Clinic',
            'crisis_center' => 'Crisis Center',
            'hotline_only' => 'Hotline Only',
            default => $this->type,
        };
    }
}