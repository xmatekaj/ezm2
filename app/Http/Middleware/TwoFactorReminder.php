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
