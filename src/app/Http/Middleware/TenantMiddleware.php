<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is authenticated and has a tenant_id
        if (auth()->check() && auth()->user()->tenant_id) {
            // Set tenant context in config for easy access
            config(['app.current_tenant_id' => auth()->user()->tenant_id]);

            // Share tenant with all views
            view()->share('currentTenant', auth()->user()->tenant);
        }

        return $next($request);
    }
}
