<?php
// app/Http/Middleware/EnsureTwoFactorVerified.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is not authenticated, redirect to login
        if (!$user) {
            return redirect()->route('login');
        }

        // If 2FA is enabled but not verified, redirect to 2FA page
        if ($user->two_factor_enabled && !$user->two_factor_verified_at) {
            return redirect()->route('two-factor.show');
        }

        // If 2FA verification is older than 24 hours, require re-verification
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