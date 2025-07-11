<?php

namespace App\Services;

use App\Models\User;
use App\Models\TwoFactorCode;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TwoFactorService
{
    /**
     * For TOTP users, we don't generate codes - they use their authenticator app
     * This method is only for email backup codes
     */
    public function generateEmailCode(User $user): TwoFactorCode
    {
        // Deactivate any existing email codes
        TwoFactorCode::where('user_id', $user->id)
            ->where('type', 'email')
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate new 6-digit code for email
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $twoFactorCode = TwoFactorCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'type' => 'email', // Always email for this method
            'expires_at' => now()->addMinutes(15), // 15 minutes for email codes
        ]);

        // Send the email code
        $this->sendEmailCode($user, $code);

        return $twoFactorCode;
    }

    /**
     * Verify TOTP code from authenticator app
     */
    public function verifyTOTPCode(User $user, string $code): bool
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        return $user->verifyTwoFactorCode($code);
    }

    /**
     * Verify email code
     */
    public function verifyEmailCode(User $user, string $code): bool
    {
        $twoFactorCode = TwoFactorCode::where('user_id', $user->id)
            ->where('code', $code)
            ->where('type', 'email')
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$twoFactorCode) {
            return false;
        }

        $twoFactorCode->markAsUsed();

        $user->update([
            'two_factor_verified_at' => now(),
        ]);

        return true;
    }

    private function sendEmailCode(User $user, string $code): void
    {
        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail($code));

            Log::info('2FA Email code sent', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send 2FA email code', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
