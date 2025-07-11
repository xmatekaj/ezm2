<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Weryfikacja dwuskładnikowa</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>
        body { font-family: 'Figtree', sans-serif; }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="mb-6">
            <a href="/" class="flex items-center justify-center">
                <img src="{{ asset('graphics/logo.svg') }}" alt="eZM2" class="w-64"
                     onerror="this.onerror=null; this.src='{{ asset('graphics/logo_size_1.png') }}';" />
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-4 text-sm text-gray-600">
                Wprowadź 6-cyfrowy kod z aplikacji uwierzytelniającej aby dokończyć logowanie.
            </div>

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('two-factor.verify') }}">
                @csrf

                <!-- 2FA Code -->
                <div class="mb-4">
                    <label for="code" class="block font-medium text-sm text-gray-700 mb-2">
                        Kod uwierzytelniający
                    </label>
                    <input id="code"
                           class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full text-center text-2xl tracking-widest py-3"
                           type="text"
                           name="code"
                           value="{{ old('code') }}"
                           required
                           autofocus
                           autocomplete="one-time-code"
                           maxlength="6"
                           placeholder="000000" />
                    @error('code')
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between mt-6">
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                       href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Wyloguj
                    </a>

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Zweryfikuj
                    </button>
                </div>
            </form>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>

            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-800 mb-2">
                    <strong>Instrukcja:</strong>
                </p>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Otwórz aplikację uwierzytelniającą (Google Authenticator, Authy, itp.)</li>
                    <li>• Znajdź wpis dla {{ config('app.name') }}</li>
                    <li>• Wprowadź aktualny 6-cyfrowy kod</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Auto-submit when 6 digits are entered
        document.getElementById('code').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length > 6) {
                value = value.substring(0, 6);
            }
            e.target.value = value;

            if (value.length === 6) {
                e.target.form.submit();
            }
        });

        // Allow only numbers
        document.getElementById('code').addEventListener('keypress', function(e) {
            if (!/\d/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Escape', 'Enter'].includes(e.key)) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
