<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorReminder
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && !$user->two_factor_enabled) {
            // Skip reminder for 2FA setup pages and AJAX requests
            $excludedRoutes = [
                'two-factor.setup',
                'two-factor.enable',
                'two-factor.show',
                'two-factor.verify',
                'logout'
            ];

            // Always show reminder for users without 2FA (except on excluded routes)
            if (!$request->ajax() &&
                !in_array($request->route()->getName(), $excludedRoutes)) {

                session()->flash('show_2fa_reminder', true);
            }
        }

        return $next($request);
    }
}


/*

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorReminder
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = Auth::user();

            if ($user && !$user->two_factor_enabled) {
                // Skip reminder for 2FA setup pages, AJAX requests, and file uploads
                $excludedRoutes = [
                    'two-factor.setup',
                    'two-factor.enable',
                    'two-factor.show',
                    'two-factor.verify',
                    'logout'
                ];

                // Skip for file uploads, AJAX requests, and API calls
                if ($request->ajax() ||
                    $request->expectsJson() ||
                    $request->hasFile('csv_file') ||
                    $request->is('api/*') ||
                    in_array($request->route()?->getName(), $excludedRoutes)) {

                    return $next($request);
                }

                // Only flash for GET requests to avoid session conflicts with POST/uploads
                if ($request->isMethod('GET')) {
                    session()->flash('show_2fa_reminder', true);
                }
            }
        } catch (\Exception $e) {
            // Log the error but don't break the request
            \Log::warning('TwoFactorReminder middleware error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'has_files' => $request->hasFile('csv_file')
            ]);
        }

        return $next($request);
    }
}


*/
