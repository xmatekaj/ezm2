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
        <div class="max-w-2xl w-full space-y-8">
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
                
                <!-- Personal Information -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Dane osobowe</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Email
                            </label>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="twoj@email.com" value="{{ old('email') }}">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">
                                Telefon komórkowy
                            </label>
                            <input id="phone" name="phone" type="tel" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="+48 123 456 789" value="{{ old('phone') }}">
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Adres</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="voivodeship" class="block text-sm font-medium text-gray-700">
                                Województwo
                            </label>
                            <select id="voivodeship" name="voivodeship" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Wybierz województwo</option>
                                <!-- Will be populated via JavaScript -->
                            </select>
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">
                                Miasto/Gmina
                            </label>
                            <select id="city" name="city" required disabled
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm disabled:bg-gray-100">
                                <option value="">Najpierw wybierz województwo</option>
                            </select>
                        </div>

                        <div>
                            <label for="street" class="block text-sm font-medium text-gray-700">
                                Ulica
                            </label>
                            <select id="street" name="street" required disabled
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm disabled:bg-gray-100">
                                <option value="">Najpierw wybierz miasto</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Building Information -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Dane budynku i mieszkania</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="building_number" class="block text-sm font-medium text-gray-700">
                                Numer budynku/klatki
                            </label>
                            <input id="building_number" name="building_number" type="text"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="np. 15A" value="{{ old('building_number') }}">
                            <p class="mt-1 text-xs text-gray-500">Opcjonalnie, jeśli budynek ma osobny numer</p>
                        </div>

                        <div>
                            <label for="apartment_number" class="block text-sm font-medium text-gray-700">
                                Numer mieszkania <span class="text-red-500">*</span>
                            </label>
                            <input id="apartment_number" name="apartment_number" type="text" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="np. 15" value="{{ old('apartment_number') }}">
                        </div>
                    </div>
                </div>

                <!-- Verification Data -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-blue-800 mb-2">
                        Dane weryfikacyjne
                    </h3>
                    <p class="text-sm text-blue-700 mb-4">
                        Podaj dane z ostatniego rozliczenia, aby zweryfikować twoją tożsamość
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="last_water_settlement_amount" class="block text-sm font-medium text-gray-700">
                                Rozliczenie wody (PLN)
                            </label>
                            <input id="last_water_settlement_amount" name="last_water_settlement_amount" type="number" step="0.01" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   value="{{ old('last_water_settlement_amount') }}">
                        </div>

                        <div>
                            <label for="last_fee_amount" class="block text-sm font-medium text-gray-700">
                                Opłata miesięczna (PLN)
                            </label>
                            <input id="last_fee_amount" name="last_fee_amount" type="number" step="0.01" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   value="{{ old('last_fee_amount') }}">
                        </div>

                        <div>
                            <label for="last_water_prediction_amount" class="block text-sm font-medium text-gray-700">
                                Prognoza wody (PLN)
                            </label>
                            <input id="last_water_prediction_amount" name="last_water_prediction_amount" type="number" step="0.01" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   value="{{ old('last_water_prediction_amount') }}">
                        </div>

                        <div>
                            <label for="current_occupants" class="block text-sm font-medium text-gray-700">
                                Liczba mieszkańców
                            </label>
                            <input id="current_occupants" name="current_occupants" type="number" min="1" max="20" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   value="{{ old('current_occupants') }}">
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Zweryfikuj dane i kontynuuj
                    </button>
                </div>
            </form>

            <div class="text-center">
                <a href="{{ route('welcome') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    ← Powrót do strony głównej
                </a>
            </div>
        </div>
    </div>

    <script>
        // JavaScript for handling territorial data selection
        document.addEventListener('DOMContentLoaded', function() {
            const voivodeshipSelect = document.getElementById('voivodeship');
            const citySelect = document.getElementById('city');
            const streetSelect = document.getElementById('street');

            // Load voivodeships on page load
            loadVoivodeships();

            voivodeshipSelect.addEventListener('change', function() {
                const voivodeshipCode = this.value;
                if (voivodeshipCode) {
                    loadCities(voivodeshipCode);
                    citySelect.disabled = false;
                } else {
                    citySelect.disabled = true;
                    streetSelect.disabled = true;
                    citySelect.innerHTML = '<option value="">Najpierw wybierz województwo</option>';
                    streetSelect.innerHTML = '<option value="">Najpierw wybierz miasto</option>';
                }
            });

            citySelect.addEventListener('change', function() {
                const voivodeshipCode = voivodeshipSelect.value;
                const cityCode = this.value;
                if (voivodeshipCode && cityCode) {
                    loadStreets(voivodeshipCode, cityCode);
                    streetSelect.disabled = false;
                } else {
                    streetSelect.disabled = true;
                    streetSelect.innerHTML = '<option value="">Najpierw wybierz miasto</option>';
                }
            });

            function loadVoivodeships() {
                fetch('/api/voivodeships')
                    .then(response => response.json())
                    .then(data => {
                        voivodeshipSelect.innerHTML = '<option value="">Wybierz województwo</option>';
                        data.forEach(voivodeship => {
                            const option = document.createElement('option');
                            option.value = voivodeship.woj;
                            option.textContent = voivodeship.nazwa;
                            voivodeshipSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading voivodeships:', error));
            }

            function loadCities(voivodeshipCode) {
                fetch(`/api/cities/${voivodeshipCode}`)
                    .then(response => response.json())
                    .then(data => {
                        citySelect.innerHTML = '<option value="">Wybierz miasto/gminę</option>';
                        data.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.pow; // Use 'pow' instead of 'gmi'
                            option.textContent = city.nazwa;
                            citySelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading cities:', error));
            }

            function loadStreets(voivodeshipCode, cityCode) {
                fetch(`/api/streets/${voivodeshipCode}/${cityCode}`)
                    .then(response => response.json())
                    .then(data => {
                        streetSelect.innerHTML = '<option value="">Wybierz ulicę</option>';
                        data.forEach(street => {
                            const option = document.createElement('option');
                            option.value = street.id;
                            option.textContent = street.full_name;
                            streetSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading streets:', error));
            }
        });
    </script>
</body>
</html>" action="{{ route('register.initiate') }}" method="POST">
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

            <div class="text-center">
                <a href="{{ route('welcome') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    ← Powrót do strony głównej
                </a>
            </div>
        </div>
    </div>
</body>
</html>