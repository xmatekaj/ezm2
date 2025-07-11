<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Apartment;
use App\Models\RegistrationVerification;
use App\Models\User;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function initiateRegistration(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'voivodeship' => 'required|string',
            'city' => 'required|string',
            'street' => 'required|string',
            'building_number' => 'nullable|string|max:10',
            'apartment_number' => 'required|string|max:10',
            'last_water_settlement_amount' => 'required|numeric|min:0',
            'last_fee_amount' => 'required|numeric|min:0',
            'last_water_prediction_amount' => 'required|numeric|min:0',
            'current_occupants' => 'required|integer|min:1|max:20',
        ]);

        // Find community and apartment based on territorial data
        $apartment = $this->findApartmentByAddress($request);

        if (!$apartment) {
            throw ValidationException::withMessages([
                'apartment_number' => 'Nie znaleziono mieszkania pod podanym adresem. Sprawdź czy wszystkie dane są prawidłowe.'
            ]);
        }

        // Verify the provided financial data
        $this->verifyData($request, $apartment);

        // Create verification record
        $verification = RegistrationVerification::create([
            'email' => $request->email,
            'community_id' => $apartment->community_id,
            'apartment_id' => $apartment->id,
            'last_water_settlement_amount' => $request->last_water_settlement_amount,
            'last_fee_amount' => $request->last_fee_amount,
            'last_water_prediction_amount' => $request->last_water_prediction_amount,
            'current_occupants' => $request->current_occupants,
            'building_number' => $request->building_number,
            'apartment_number' => $request->apartment_number,
            'phone' => $request->phone,
            'voivodeship' => $request->voivodeship,
            'city' => $request->city,
            'street' => $request->street,
        ]);

        return redirect()->route('register.complete', $verification->verification_token)
            ->with('success', 'Dane zostały zweryfikowane. Uzupełnij dane rejestracji.');
    }

    public function showCompleteForm($token)
    {
        $verification = RegistrationVerification::where('verification_token', $token)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return view('auth.register-complete', compact('verification'));
    }

    public function completeRegistration(Request $request, $token)
    {
        $verification = RegistrationVerification::where('verification_token', $token)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'two_factor_method' => 'required|in:email,sms',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::transaction(function () use ($request, $verification) {
            // Create user (everyone starts as 'owner', admin decides permissions later)
            $user = User::create([
                'email' => $verification->email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'name' => $request->first_name . ' ' . $request->last_name,
                'phone' => $verification->phone,
                'password' => Hash::make($request->password),
                'user_type' => 'owner',
                'two_factor_enabled' => true,
                'two_factor_method' => $request->two_factor_method,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Link user to community
            $user->communities()->attach($verification->community_id, [
                'access_type' => 'owner',
                'is_active' => true,
                'verified_at' => now(),
            ]);

            // Link to person record if exists
            $person = Person::where('email', $verification->email)->first();
            if ($person) {
                $user->update(['person_id' => $person->id]);
            }

            $verification->update([
                'is_verified' => true,
                'verified_at' => now(),
            ]);
        });

        return redirect()->route('login')
            ->with('success', 'Konto zostało utworzone pomyślnie. Możesz się teraz zalogować.');
    }

    private function findApartmentByAddress(Request $request)
    {
        // For now, we'll use a simple approach - find apartments by building/apartment number
        // In a real implementation, you'd match the territorial data to community addresses

        $query = Apartment::query();

        if ($request->building_number) {
            $query->where('building_number', $request->building_number);
        }

        $query->where('apartment_number', $request->apartment_number);

        // TODO: Add community matching based on territorial data
        // For now, return the first matching apartment
        return $query->first();
    }

    private function verifyData(Request $request, Apartment $apartment)
    {
        // Verify financial data against actual records

        // 1. Check current occupancy
        $currentOccupancy = $apartment->occupancyHistory()
            ->where('change_date', '<=', now())
            ->orderBy('change_date', 'desc')
            ->first();

        if ($currentOccupancy && $currentOccupancy->number_of_occupants != $request->current_occupants) {
            throw ValidationException::withMessages([
                'current_occupants' => 'Liczba mieszkańców nie jest zgodna z naszymi danymi.'
            ]);
        }

        // 2. Verify last fee amount against current pricing
        $currentPrice = $apartment->community->prices()
            ->where('change_date', '<=', now())
            ->orderBy('change_date', 'desc')
            ->first();

        if ($currentPrice) {
            $expectedFee = $currentPrice->calculateApartmentFee($apartment);
            $tolerance = $expectedFee * 0.1; // 10% tolerance

            if (abs($request->last_fee_amount - $expectedFee) > $tolerance) {
                throw ValidationException::withMessages([
                    'last_fee_amount' => 'Kwota ostatniej opłaty nie jest zgodna z naszymi danymi.'
                ]);
            }
        }

        // 3. Verify water amounts (basic check)
        if ($request->last_water_settlement_amount < 0 || $request->last_water_prediction_amount < 0) {
            throw ValidationException::withMessages([
                'last_water_settlement_amount' => 'Kwoty wodne muszą być nieujemne.'
            ]);
        }

        return true;
    }
}
