<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Guards the browser facing staff pages.
 *
 * RoleMiddleware answers with JSON, which is right for the API but useless in
 * a browser. This one redirects guests to the login screen and blocks anyone
 * whose role is not listed, e.g. 'staff:moderator,admin'.
 */
class StaffAccess
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (! Auth::check()) {
            return redirect()->guest(route('staff.login'));
        }

        if (! empty($roles) && ! in_array(Auth::user()->role, $roles, true)) {
            abort(403, 'You do not have permission to open this workspace.');
        }

        return $next($request);
    }
}
