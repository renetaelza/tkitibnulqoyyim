<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // DB enum uses 'superadmin' but keep backward-compatibility with 'super_admin'
        $role = auth()->user()?->role;
        if (!auth()->check() || !in_array($role, ['superadmin', 'super_admin'], true)) {
            abort(403);
        }

        return $next($request);
    }
}
