<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformPolicy extends Model
{
    protected $fillable = [
        'platform',
        'reporting_url',
        'instructions',
        'last_verified_at',
    ];

    protected $casts = [
        'last_verified_at' => 'date',
    ];

    /**
     * Determine whether this policy needs verification.
     *
     * A policy is considered outdated when it has not
     * been verified for more than 90 days.
     */
    public function needsReview(): bool
    {
        if ($this->last_verified_at === null) {
            return true;
        }

        return $this->last_verified_at->lt(now()->subDays(90));
    }
}