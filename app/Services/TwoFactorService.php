<?php

namespace App\Services;

use App\Models\User;
use App\Models\TwoFactorCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TwoFactorService
{
    public function generateCode(User $user): TwoFactorCode
    {
        // Deactivate any existing codes
        TwoFactorCode::where('user_id', $user->id)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate new 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $twoFactorCode = TwoFactorCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'type' => $user->two_factor_method,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send the code
        $this->sendCode($user, $code);

        return $twoFactorCode;
    }

    public function verifyCode(User $user, string $code): bool
    {
        $twoFactorCode = TwoFactorCode::where('user_id', $user->id)
            ->where('code', $code)
            ->valid()
            ->first();

        if (!$twoFactorCode) {
            return false;
        }

        $twoFactorCode->markAsUsed();
        
        $user->update([
            'two_factor_verified_at' => now(),
            'last_login_at' => now(),
        ]);

        return true;
    }

    private function sendCode(User $user, string $code): void
    {
        if ($user->two_factor_method === 'email') {
            $this->sendEmailCode($user, $code);
        } elseif ($user->two_factor_method === 'sms') {
            $this->sendSMSCode($user, $code);
        }
    }

    private function sendEmailCode(User $user, string $code): void
    {
        try {
            // For now, we'll just log the code - you can implement email sending later
            Log::info('2FA Email code generated', [
                'user_id' => $user->id,
                'email' => $user->email,
                'code' => $code, // Remove this in production
            ]);

            // TODO: Implement actual email sending
            // Mail::send('emails.two-factor-code', [
            //     'user' => $user,
            //     'code' => $code,
            //     'expires_in' => 10, // minutes
            // ], function ($message) use ($user) {
            //     $message->to($user->email)
            //             ->subject('Kod weryfikacyjny - EZM');
            // });
        } catch (\Exception $e) {
            Log::error('Failed to send 2FA email code', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendSMSCode(User $user, string $code): void
    {
        try {
            // For now, we'll just log the code - you can implement SMS sending later
            Log::info('2FA SMS code generated', [
                'user_id' => $user->id,
                'phone' => $user->phone,
                'code' => $code, // Remove this in production
            ]);

            // TODO: Implement SMS sending here
            // You can use services like Twilio, Vonage (Nexmo), or local SMS providers
            
        } catch (\Exception $e) {
            Log::error('Failed to send 2FA SMS code', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}