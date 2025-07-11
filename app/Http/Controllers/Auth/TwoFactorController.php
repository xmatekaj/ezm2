<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    /**
     * Show the 2FA verification form
     */
    public function show()
    {
        $user = Auth::user();

        if (!$user || $user->two_factor_verified_at) {
            return redirect()->intended('/admin');
        }

        return view('auth.two-factor', compact('user'));
    }

    /**
     * Verify 2FA code (TOTP or Email)
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $code = $request->code;
        $verified = false;

        // Try TOTP first
        if ($user->two_factor_secret && $user->verifyTwoFactorCode($code)) {
            $verified = true;
        }
        // Try email code
        elseif ($user->verifyEmailCode($code)) {
            $verified = true;
            $user->clearEmailVerificationCode();
        }
        // Try recovery code
        elseif ($user->useRecoveryCode($code)) {
            $verified = true;
        }

        if (!$verified) {
            throw ValidationException::withMessages([
                'code' => ['Nieprawidłowy kod. Spróbuj ponownie.'],
            ]);
        }

        // Mark as verified for this session
        $user->markTwoFactorVerified();

        return redirect()->intended('/admin');
    }


    /**
     * Show recovery code form
     */
    public function showRecoveryForm()
    {
        $user = Auth::user();

        if (!$user || $user->two_factor_verified_at) {
            return redirect()->intended('/admin');
        }

        return view('auth.two-factor-recovery');
    }

    /**
     * Verify recovery code
     */
    public function verifyRecovery(Request $request)
    {
        $request->validate([
            'recovery_code' => 'required|string',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->useRecoveryCode($request->recovery_code)) {
            throw ValidationException::withMessages([
                'recovery_code' => ['Nieprawidłowy kod odzyskiwania.'],
            ]);
        }

        // Mark as verified for this session
        $user->markTwoFactorVerified();

        return redirect()->intended('/admin')
                        ->with('warning', 'Użyłeś kod odzyskiwania. Rozważ wygenerowanie nowych kodów.');
    }
}
