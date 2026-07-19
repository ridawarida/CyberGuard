<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotline extends Model
{
    use HasFactory;

    protected $fillable = [
        'help_center_id',
        'name',
        'phone_number',
        'is_toll_free',
        'description',
        'operating_hours',
        'is_active',
    ];

    protected $casts = [
        'operating_hours' => 'array',
        'is_toll_free' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function helpCenter()
    {
        return $this->belongsTo(HelpCenter::class);
    }
}