<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Module 3 feature owner: Johra-E-Jannat Oishy.
 */
class ConsultationMessage extends Model
{
    protected $fillable = [
        'consultation_id',
        'sender_type',
        'sender_id',
        'body',
    ];

    /**
     * Bumps the parent Consultation's updated_at whenever a message is
     * saved. Without this, the moderator list's "sort by most recently
     * active" and "Last Activity" column would silently just show each
     * consultation's creation time forever, since nothing else ever
     * touches the Consultation row directly.
     */
    protected $touches = ['consultation'];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
