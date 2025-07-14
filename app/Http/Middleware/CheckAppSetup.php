<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAppSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip setup check for setup routes and API routes
        if ($request->is('admin/settings*') || $request->is('api/*') || $request->is('setup*')) {
            return $next($request);
        }

        // Check if app is initialized
        $isInitialized = Setting::get('app_initialized', false);
        
        if (!$isInitialized && $request->is('admin*')) {
            // Redirect to settings page for first-time setup
            return redirect('/admin/settings')->with('warning', 'Skonfiguruj dane zarządcy, aby rozpocząć pracę z aplikacją.');
        }

        return $next($request);
    }
}