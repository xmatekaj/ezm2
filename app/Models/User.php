<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PragmaRX\Google2FA\Google2FA;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'two_factor_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'email_verification_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_verified_at' => 'datetime',
            'email_verification_code_expires_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
        ];
    }

    /**
     * Get the recovery codes as an array
     */
    public function getRecoveryCodesAttribute()
    {
        return $this->two_factor_recovery_codes
            ? json_decode($this->two_factor_recovery_codes, true)
            : [];
    }

    /**
     * Set the recovery codes from an array
     */
    public function setRecoveryCodesAttribute($value)
    {
        $this->attributes['two_factor_recovery_codes'] = is_array($value)
            ? json_encode($value)
            : $value;
    }

    /**
     * Generate new 2FA secret
     */
    public function generateTwoFactorSecret()
    {
        $google2fa = new Google2FA();
        $this->two_factor_secret = $google2fa->generateSecretKey();
        $this->save();

        return $this->two_factor_secret;
    }

    /**
     * Generate recovery codes
     */
    public function generateRecoveryCodes()
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10));
        }

        $this->recovery_codes = $codes;
        $this->save();

        return $codes;
    }

    /**
     * Verify 2FA code
     */
    public function verifyTwoFactorCode($code)
    {
        if (!$this->two_factor_secret) {
            return false;
        }

        $google2fa = new Google2FA();
        return $google2fa->verifyKey($this->two_factor_secret, $code);
    }

    /**
     * Use recovery code
     */
    public function useRecoveryCode($code)
    {
        $codes = $this->recovery_codes;

        if (($key = array_search(strtoupper($code), $codes)) !== false) {
            unset($codes[$key]);
            $this->recovery_codes = array_values($codes);
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Generate email verification code
     */
    public function generateEmailVerificationCode()
    {
        $this->email_verification_code = sprintf('%06d', random_int(0, 999999));
        $this->email_verification_code_expires_at = now()->addMinutes(15);
        $this->save();

        return $this->email_verification_code;
    }

    /**
     * Verify email code
     */
    public function verifyEmailCode($code)
    {
        return $this->email_verification_code === $code
            && $this->email_verification_code_expires_at
            && $this->email_verification_code_expires_at->isFuture();
    }

    /**
     * Clear email verification code
     */
    public function clearEmailVerificationCode()
    {
        $this->email_verification_code = null;
        $this->email_verification_code_expires_at = null;
        $this->save();
    }

    /**
     * Check if 2FA is required for this user
     */
    public function requiresTwoFactor()
    {
        return $this->two_factor_enabled && !$this->two_factor_verified_at;
    }

    /**
     * Mark 2FA as verified for this session
     */
    public function markTwoFactorVerified()
    {
        $this->two_factor_verified_at = now();
        $this->save();
    }

    /**
     * Get QR Code URL for Google Authenticator
     */
    public function getQrCodeUrl()
    {
        if (!$this->two_factor_secret) {
            return null;
        }

        $google2fa = new Google2FA();
        return $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->email,
            $this->two_factor_secret
        );
    }

    /**
     * Check if user is a company user (admin, accountant, technician, etc.)
     */
    public function isCompanyUser()
    {
        return in_array($this->user_type, ['super_admin', 'admin', 'accountant', 'technician', 'manager']);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin()
    {
        return $this->user_type === 'super_admin';
    }

    /**
     * Check if user is an apartment owner
     */
    public function isOwner()
    {
        return $this->user_type === 'owner';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    /**
     * Check if user is accountant
     */
    public function isAccountant()
    {
        return $this->user_type === 'accountant';
    }

    /**
     * Check if user is technician
     */
    public function isTechnician()
    {
        return $this->user_type === 'technician';
    }

    /**
     * Get user's dashboard route based on role
     */
    public function getDashboardRoute()
    {
        return match($this->user_type) {
            'super_admin' => '/admin',
            'admin' => '/admin',
            'accountant' => '/admin/financial-transactions',
            'technician' => '/admin/water-meters',
            'manager' => '/admin/communities',
            'owner' => '/owner/dashboard',
            default => '/profile'
        };
    }

    /**
     * Get user's allowed navigation items
     */
    public function getAllowedNavigation()
    {
        return match($this->user_type) {
            'admin' => [
                'communities', 'apartments', 'people', 'financial-transactions',
                'water-meters', 'prices', 'reports'
            ],
            'accountant' => [
                'financial-transactions', 'apartments', 'people', 'reports'
            ],
            'technician' => [
                'water-meters', 'apartments', 'communities'
            ],
            'manager' => [
                'communities', 'apartments', 'people'
            ],
            'owner' => [
                'my-apartment', 'payments', 'water-readings', 'documents'
            ],
            default => []
        };
    }
}
