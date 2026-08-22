<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanicEvent extends Model
{
    use HasFactory;

    protected $table = 'panic_events';

    protected $fillable = [
        'trigger_source',
        'context',
    ];

    public const SOURCES = ['click', 'hotkey', 'fallback'];

    public const CONTEXTS = ['public', 'wizard', 'case_file', 'dashboard', 'unknown'];

    /**
     * Monthly totals for the admin metrics screen.
     */
    public function scopeInMonth($query, int $year, int $month)
    {
        return $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
    }
}
