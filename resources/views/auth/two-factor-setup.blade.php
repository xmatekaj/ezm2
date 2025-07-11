<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Konfiguracja 2FA</title>

    <script src="https://cdn.tailwindcss.com"></script>

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

        <div class="w-full sm:max-w-2xl mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Konfiguracja uwierzytelniania dwuskładnikowego</h2>
                <p class="text-sm text-gray-600 mt-2">
                    Zwiększ bezpieczeństwo swojego konta poprzez włączenie uwierzytelniania dwuskładnikowego.
                </p>
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

            <div class="grid md:grid-cols-2 gap-6">
                <!-- QR Code Section -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Krok 1: Zeskanuj kod QR</h3>
                    <div class="text-center">
                        <div class="inline-block p-4 bg-white rounded-lg shadow">
                            {!! $qrCodeSvg !!}
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-4">
                        Użyj aplikacji uwierzytelniającej (Google Authenticator, Authy, itp.) aby zeskanować kod QR.
                    </p>
                </div>

                <!-- Manual Entry Section -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Alternatywnie: Wprowadź ręcznie</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Jeśli nie możesz zeskanować kodu QR, wprowadź ten klucz ręcznie w swojej aplikacji:
                    </p>
                    <div class="bg-white p-3 rounded border font-mono text-sm break-all">
                        {{ $manualKey }}
                    </div>
                    <button onclick="copyToClipboard('{{ $manualKey }}')"
                            class="mt-2 text-sm text-indigo-600 hover:text-indigo-800">
                        📋 Skopiuj klucz
                    </button>
                </div>
            </div>

            <!-- Verification Form -->
            <div class="mt-8 p-6 bg-blue-50 rounded-lg">
                <h3 class="text-lg font-semibold mb-4">Krok 2: Zweryfikuj konfigurację</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Po dodaniu konta do aplikacji, wprowadź 6-cyfrowy kod aby potwierdzić poprawność konfiguracji:
                </p>

                <form method="POST" action="{{ route('two-factor.enable') }}">
                    @csrf
                    <div class="flex gap-4 items-end">
                        <div class="flex-1">
                            <label for="code" class="block font-medium text-sm text-gray-700 mb-2">
                                Kod z aplikacji
                            </label>
                            <input id="code"
                                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-center text-xl tracking-widest py-2"
                                   type="text"
                                   name="code"
                                   required
                                   autofocus
                                   autocomplete="one-time-code"
                                   maxlength="6"
                                   placeholder="000000" />
                        </div>
                        <button type="submit"
                                class="px-6 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Włącz 2FA
                        </button>
                    </div>
                </form>
            </div>

            <!-- Apps Info -->
            <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                <h4 class="font-semibold text-yellow-800 mb-2">Zalecane aplikacje uwierzytelniające:</h4>
                <ul class="text-sm text-yellow-700 space-y-1">
                    <li>• <strong>Google Authenticator</strong> (Android/iOS)</li>
                    <li>• <strong>Authy</strong> (Android/iOS/Desktop)</li>
                    <li>• <strong>Microsoft Authenticator</strong> (Android/iOS)</li>
                    <li>• <strong>1Password</strong> (Premium)</li>
                </ul>
            </div>

            <div class="mt-6 flex justify-between">
                <a href="/admin"
                   class="text-sm text-gray-600 hover:text-gray-900 underline">
                    ← Pomiń na razie
                </a>
                <a href="/profile"
                   class="text-sm text-indigo-600 hover:text-indigo-800 underline">
                    Zarządzaj profilem →
                </a>
            </div>
        </div>
    </div>

    <script>
        // Only allow numbers in code input
        document.getElementById('code').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 6) {
                value = value.substring(0, 6);
            }
            e.target.value = value;
        });

        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Klucz skopiowany do schowka!');
            });
        }
    </script>
</body>
</html>
