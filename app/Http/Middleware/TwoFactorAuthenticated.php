<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Only check 2FA verification if user has 2FA enabled
        if ($user->two_factor_enabled && !$user->two_factor_verified_at) {
            return redirect()->route('two-factor.show');
        }

        // Reset 2FA verification if it's older than 24 hours for security
        if ($user->two_factor_enabled && 
            $user->two_factor_verified_at && 
            $user->two_factor_verified_at < now()->subHours(24)) {
            
            $user->update(['two_factor_verified_at' => null]);
            return redirect()->route('two-factor.show')
                ->with('info', 'Wymagana jest ponowna weryfikacja 2FA.');
        }

        return $next($request);
    }
}