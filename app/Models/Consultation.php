<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Digital Safe Space - Secure Consultation Workspace.
 * Module 3 feature owner: Johra-E-Jannat Oishy.
 *
 * One row per incident's chat thread. The access_key is how a victim gets
 * in without an account - it is generated here, never accepted from
 * outside input, the same defensive instinct as PanicSetting/CalmSetting
 * never trusting client-supplied values for anything security sensitive.
 */
class Consultation extends Model
{
    protected $fillable = [
        'incident_id',
        'access_key',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (Consultation $consultation) {
            if (empty($consultation->access_key)) {
                $consultation->access_key = self::generateUniqueAccessKey();
            }

            if (empty($consultation->status)) {
                $consultation->status = 'open';
            }
        });
    }

    public static function generateUniqueAccessKey(): string
    {
        do {
            $key = Str::random(40);
        } while (self::where('access_key', $key)->exists());

        return $key;
    }

    public static function findByAccessKey(string $key): ?self
    {
        return self::where('access_key', $key)->first();
    }

    /**
     * Get-or-create by incident. Doubles as the fallback path for when
     * IncidentObserver did not run (e.g. an incident inserted outside
     * Eloquent) - mirrors panic.js's "the escape continues regardless",
     * a missing chat session should never be a dead end for the victim -
     * and as the safe way for Ishrat's confirmation page to fetch the
     * access key regardless of whether the observer already fired.
     */
    public static function forIncident(int $incidentId): self
    {
        return self::firstOrCreate(['incident_id' => $incidentId]);
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConsultationMessage::class)->orderBy('created_at');
    }
}
