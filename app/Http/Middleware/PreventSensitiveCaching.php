<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Companion middleware for the Quick Escape Panic Button.
 *
 * Without this, an abuser can press the browser back button after the escape
 * and the previous page is served straight from the back forward cache, with
 * the victim's typed narrative still on screen. Marking the response no-store
 * forces the browser to re-request the page, which now has no session behind
 * it and therefore shows nothing.
 *
 * Apply it to any route that renders victim content, for example:
 *   Route::get('/incident/wizard/step2', ...)
 *       ->middleware(\App\Http\Middleware\PreventSensitiveCaching::class);
 */
class PreventSensitiveCaching
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        // Keeps the page out of search engine caches and referrer logs too.
        $response->headers->set('Referrer-Policy', 'no-referrer');

        return $response;
    }
}
