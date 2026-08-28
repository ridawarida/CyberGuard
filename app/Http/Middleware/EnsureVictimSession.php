<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Module 3 feature owner: Johra-E-Jannat Oishy.
 *
 * Register this alias the same way `role` is registered for
 * role:admin / role:moderator (Laravel 11+: $middleware->alias([...]) in
 * bootstrap/app.php; older apps: $routeMiddleware in Kernel.php):
 *
 *     'victim.session' => \App\Http\Middleware\EnsureVictimSession::class,
 */
class EnsureVictimSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('consultation_id')) {
            // Remembered so a bookmarked link (e.g. straight to the PDF
            // export page) lands back on itself after the key is entered,
            // instead of always dropping the victim on the chat page.
            if ($request->isMethod('get')) {
                $request->session()->put('victim.intended_url', $request->fullUrl());
            }

            return redirect()
                ->route('consult.access')
                ->withErrors(['access_key' => 'Please enter your access key to continue.']);
        }

        return $next($request);
    }
}
