<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecoveryJournalEntry extends Model
{
    use HasFactory;

    protected $fillable = ['summary', 'stress_level'];

    protected $casts = ['stress_level' => 'integer'];

    public function journal()
    {
        return $this->belongsTo(RecoveryJournal::class, 'recovery_journal_id');
    }
}