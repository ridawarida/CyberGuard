<?php

namespace App\Observers;

use App\Models\Consultation;
use App\Models\Incident;

/**
 * Module 3 feature owner: Johra-E-Jannat Oishy.
 *
 * Satisfies the spec's "access key generated when a victim submits a
 * report" without needing to edit Ishrat's intake wizard controller at
 * all - it listens for the Incident model itself, so it fires no matter
 * which controller (or console command, or seeder) creates the row.
 *
 * WIRE-UP: add this one line to your AppServiceProvider::boot(), next to
 * wherever any other model observers are registered:
 *
 *     Incident::observe(IncidentObserver::class);
 *
 * If your report model isn't named Incident, point the observe() call
 * (and the `Incident` import here and in Consultation::incident()) at
 * whatever it's actually called.
 */
class IncidentObserver
{
    public function created(Incident $incident): void
    {
        Consultation::firstOrCreate(['incident_id' => $incident->id]);
    }
}
