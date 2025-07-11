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

            if (!$request->ajax() &&
                !in_array($request->route()->getName(), $excludedRoutes) &&
                !$request->session()->has('2fa_reminder_dismissed_' . $user->id)) {

                session()->flash('show_2fa_reminder', true);
            }
        }

        return $next($request);
    }
}
