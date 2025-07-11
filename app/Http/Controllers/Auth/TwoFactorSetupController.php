<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorSetupController extends Controller
{
    /**
     * Show the 2FA setup page
     */
    public function show()
    {
        $user = Auth::user();
        
        // Generate secret if not exists
        if (!$user->two_factor_secret) {
            $user->generateTwoFactorSecret();
        }

        $qrCodeUrl = $user->getQrCodeUrl();
        $qrCodeSvg = QrCode::size(200)->generate($qrCodeUrl);

        return view('auth.two-factor-setup', [
            'user' => $user,
            'qrCodeSvg' => $qrCodeSvg,
            'manualKey' => $user->two_factor_secret
        ]);
    }

    /**
     * Enable 2FA after verification
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if (!$user->verifyTwoFactorCode($request->code)) {
            throw ValidationException::withMessages([
                'code' => ['Nieprawidłowy kod. Spróbuj ponownie.'],
            ]);
        }

        // Enable 2FA and generate recovery codes
        $user->two_factor_enabled = true;
        $user->save();
        
        $recoveryCodes = $user->generateRecoveryCodes();

        return redirect()->route('two-factor.recovery-codes')
                        ->with('success', 'Uwierzytelnianie dwuskładnikowe zostało włączone!')
                        ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * Show recovery codes
     */
    public function showRecoveryCodes()
    {
        $user = Auth::user();
        
        if (!$user->two_factor_enabled) {
            return redirect()->route('two-factor.setup');
        }

        $recoveryCodes = session('recovery_codes', $user->recovery_codes);

        return view('auth.two-factor-recovery-codes', [
            'recoveryCodes' => $recoveryCodes
        ]);
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->two_factor_enabled) {
            return redirect()->route('two-factor.setup');
        }

        $recoveryCodes = $user->generateRecoveryCodes();

        return redirect()->route('two-factor.recovery-codes')
                        ->with('success', 'Nowe kody odzyskiwania zostały wygenerowane!')
                        ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();
        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_verified_at = null;
        $user->save();

        return redirect()->route('profile.edit')
                        ->with('success', 'Uwierzytelnianie dwuskładnikowe zostało wyłączone.');
    }

    /**
     * Show 2FA status in profile
     */
    public function status()
    {
        $user = Auth::user();

        return view('auth.two-factor-status', [
            'user' => $user
        ]);
    }
}