<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards organization-scoped routes: a user without an active organization
 * (or a Super Admin acting outside any organization) is redirected instead
 * of hitting pages/queries that assume a tenant context exists.
 *
 * Register on route groups once feature routes exist, e.g.:
 *   Route::middleware(['auth', 'ensure-organization'])->group(...)
 */
class EnsureUserHasOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->is_super_admin && $user->current_organization_id === null) {
            abort(403, 'Your account is not assigned to an organization yet.');
        }

        return $next($request);
    }
}
