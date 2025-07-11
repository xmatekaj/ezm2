<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Redirect user to appropriate dashboard based on role
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Redirect to role-specific dashboard
        return redirect($user->getDashboardRoute());
    }

    /**
     * Show owner dashboard
     */
    public function ownerDashboard()
    {
        $user = Auth::user();

        if (!$user->isOwner()) {
            abort(403, 'Unauthorized');
        }

        // Get user's apartment(s) if linked via person_id
        $apartments = [];
        if ($user->person_id) {
            $person = \App\Models\Person::find($user->person_id);
            if ($person) {
                $apartments = $person->apartments;
            }
        }

        return view('owner.dashboard', [
            'user' => $user,
            'apartments' => $apartments
        ]);
    }

    /**
     * Dismiss 2FA reminder for this session
     */
    public function dismiss2FAReminder(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            session(['2fa_reminder_dismissed_' . $user->id => true]);
        }

        return response()->json(['success' => true]);
    }
}
