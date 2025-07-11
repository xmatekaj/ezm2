<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Profil użytkownika</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>
        body { font-family: 'Figtree', sans-serif; }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-100">
    <div class="min-h-screen py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <img src="{{ asset('graphics/logo.svg') }}" alt="eZM2" class="w-12 h-12 mr-4"
                                 onerror="this.onerror=null; this.src='{{ asset('graphics/logo_size_1.png') }}';" />
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Profil użytkownika</h1>
                                <p class="text-sm text-gray-600">Zarządzaj swoim kontem i ustawieniami bezpieczeństwa</p>
                            </div>
                        </div>
                        <a href="{{ auth()->user()->getDashboardRoute() }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700">
                            ← Panel główny
                        </a>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    {{ session('warning') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Profile Information -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Informacje o profilu</h3>

                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Imię i nazwisko</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Adres email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="pt-4">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700">
                                        Zaktualizuj profil
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Zmiana hasła</h3>

                        <form method="POST" action="{{ route('profile.password') }}">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-4">
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700">Aktualne hasło</label>
                                    <input type="password" name="current_password" id="current_password"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('current_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">Nowe hasło</label>
                                    <input type="password" name="password" id="password"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Potwierdź nowe hasło</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <div class="pt-4">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-red-700">
                                        Zmień hasło
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Two-Factor Authentication -->
                <div class="bg-white overflow-hidden shadow rounded-lg md:col-span-2">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Uwierzytelnianie dwuskładnikowe (2FA)</h3>

                        @if($user->two_factor_enabled)
                            <!-- 2FA Enabled -->
                            <div class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg mb-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-green-800">Uwierzytelnianie dwuskładnikowe jest włączone</h4>
                                        <p class="text-sm text-green-700">Twoje konto jest zabezpieczone dodatkową warstwą ochrony.</p>
                                    </div>
                                </div>
                                <div class="text-green-400">🔒</div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <a href="{{ route('two-factor.recovery-codes') }}"
                                   class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700">
                                    📋 Kody odzyskiwania ({{ $recoveryCodesCount }})
                                </a>

                                <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Czy na pewno chcesz wygenerować nowe kody odzyskiwania?')"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-yellow-700">
                                        🔄 Nowe kody
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('two-factor.disable') }}" class="inline">
                                    @csrf
                                    <div class="flex">
                                        <input type="password" name="password" placeholder="Potwierdź hasłem"
                                               class="flex-1 mr-2 border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 text-sm">
                                        <button type="submit"
                                                onclick="return confirm('Czy na pewno chcesz wyłączyć 2FA? To obniży bezpieczeństwo konta.')"
                                                class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-red-700">
                                            🚫 Wyłącz
                                        </button>
                                    </div>
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </form>
                            </div>
                        @else
                            <!-- 2FA Disabled -->
                            <div class="flex items-center justify-between p-4 bg-yellow-50 border border-yellow-200 rounded-lg mb-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-yellow-800">Uwierzytelnianie dwuskładnikowe jest wyłączone</h4>
                                        <p class="text-sm text-yellow-700">Włącz 2FA aby zwiększyć bezpieczeństwo swojego konta.</p>
                                    </div>
                                </div>
                                <div class="text-yellow-400">⚠️</div>
                            </div>

                            <div class="flex justify-center">
                                <a href="{{ route('two-factor.setup') }}"
                                   class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700">
                                    🔐 Włącz uwierzytelnianie dwuskładnikowe
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
