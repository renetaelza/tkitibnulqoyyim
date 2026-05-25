<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Ensure the authenticated user has one of the allowed roles.
     *
     * Usage: ->middleware('ensure.role:superadmin,administration')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            abort(403);
        }

        $role = (string) (auth()->user()?->role ?? '');

        // Backward compatibility (older code sometimes used 'super_admin').
        if ($role === 'super_admin') {
            $role = 'superadmin';
        }

        $allowed = array_map(static function (string $r): string {
            return $r === 'super_admin' ? 'superadmin' : $r;
        }, $roles);

        if (!in_array($role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
