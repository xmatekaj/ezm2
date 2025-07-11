<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CustomLoginController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $credentials['is_active'] = true; // Only allow active users

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            if ($user->two_factor_enabled) {

                return redirect()->route('two-factor.show')
                    ->with('success', 'Kod weryfikacyjny został wysłany na ' .
                        ($user->two_factor_method === 'sms' ? 'SMS' : 'email') . '.');
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            return redirect()->intended('/dashboard')
                ->with('success', 'Zalogowano pomyślnie.');
        }

        throw ValidationException::withMessages([
            'email' => 'Nieprawidłowe dane logowania.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome')
            ->with('success', 'Wylogowano pomyślnie.');
    }
}
