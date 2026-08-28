<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Models\Incident;
use App\Observers\IncidentObserver;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The project styles everything with Bootstrap 5, but Laravel's default
        // paginator renders Tailwind markup. Switch it so pagination links in
        // the moderation workspace match the rest of the UI.
        Paginator::useBootstrapFive();
        Incident::observe(IncidentObserver::class);
    }
}
