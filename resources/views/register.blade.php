<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja - EZM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Zarejestruj się
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                        Masz już konto? Zaloguj się
                    </a>
                </p>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Wystąpiły błędy:
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form class="mt-8 space-y-6" action="{{ route('register.initiate') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email
                        </label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="twoj@email.com" value="{{ old('email') }}">
                    </div>

                    <div>
                        <label for="community_id" class="block text-sm font-medium text-gray-700">
                            Wspólnota
                        </label>
                        <select id="community_id" name="community_id" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Wybierz wspólnotę</option>
                            @foreach($communities as $community)
                                <option value="{{ $community->id }}" {{ old('community_id') == $community->id ? 'selected' : '' }}>
                                    {{ $community->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="apartment_number" class="block text-sm font-medium text-gray-700">
                            Numer mieszkania
                        </label>
                        <input id="apartment_number" name="apartment_number" type="text" required
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="np. 15 lub 2/15" value="{{ old('apartment_number') }}">
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <h3 class="text-sm font-medium text-blue-800 mb-2">
                            Dane weryfikacyjne
                        </h3>
                        <p class="text-xs text-blue-700 mb-3">
                            Podaj dane z ostatniego rozliczenia, aby zweryfikować twoją tożsamość
                        </p>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="last_water_settlement_amount" class="block text-xs font-medium text-gray-700">
                                    Rozliczenie wody (PLN)
                                </label>
                                <input id="last_water_settlement_amount" name="last_water_settlement_amount" type="number" step="0.01" required
                                       class="mt-1 block w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       value="{{ old('last_water_settlement_amount') }}">
                            </div>

                            <div>
                                <label for="last_fee_amount" class="block text-xs font-medium text-gray-700">
                                    Opłata miesięczna (PLN)
                                </label>
                                <input id="last_fee_amount" name="last_fee_amount" type="number" step="0.01" required
                                       class="mt-1 block w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       value="{{ old('last_fee_amount') }}">
                            </div>

                            <div>
                                <label for="last_water_prediction_amount" class="block text-xs font-medium text-gray-700">
                                    Prognoza wody (PLN)
                                </label>
                                <input id="last_water_prediction_amount" name="last_water_prediction_amount" type="number" step="0.01" required
                                       class="mt-1 block w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       value="{{ old('last_water_prediction_amount') }}">
                            </div>

                            <div>
                                <label for="current_occupants" class="block text-xs font-medium text-gray-700">
                                    Liczba mieszkańców
                                </label>
                                <input id="current_occupants" name="current_occupants" type="number" min="1" max="20" required
                                       class="mt-1 block w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       value="{{ old('current_occupants') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Zweryfikuj dane
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>