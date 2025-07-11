<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    public function show()
    {
        $user = Auth::user();

        if (!$user || $user->two_factor_verified_at) {
            return redirect()->intended('/dashboard');
        }

        return view('auth.two-factor', compact('user'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($this->twoFactorService->verifyCode($user, $request->code)) {
            return redirect()->intended('/dashboard')
                ->with('success', 'Zalogowano pomyślnie.');
        }

        throw ValidationException::withMessages([
            'code' => 'Nieprawidłowy lub wygasły kod weryfikacyjny.',
        ]);
    }

    public function resend()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $this->twoFactorService->generateCode($user);

        return back()->with('success', 'Nowy kod został wysłany.');
    }
}
