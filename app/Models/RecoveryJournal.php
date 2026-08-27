<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class RecoveryJournal extends Model
{
    use HasFactory;

    protected $fillable = ['access_code_hash'];

    protected $hidden = ['access_code_hash'];

    public function entries()
    {
        return $this->hasMany(RecoveryJournalEntry::class);
    }

    public function matchesAccessCode(string $accessCode): bool
    {
        return Hash::check($accessCode, $this->access_code_hash);
    }
}