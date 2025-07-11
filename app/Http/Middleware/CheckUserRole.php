<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has any of the required roles
        if (!empty($roles) && !in_array($user->user_type, $roles)) {
            // Redirect based on user type
            return redirect($user->getDashboardRoute())
                ->with('error', 'Nie masz uprawnień do tej sekcji.');
        }

        return $next($request);
    }
}
