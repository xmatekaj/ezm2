<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Kody odzyskiwania 2FA</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>
        body { font-family: 'Figtree', sans-serif; }
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="mb-6 no-print">
            <a href="/" class="flex items-center justify-center">
                <img src="{{ asset('graphics/logo.svg') }}" alt="eZM2" class="w-64" 
                     onerror="this.onerror=null; this.src='{{ asset('graphics/logo_size_1.png') }}';" />
            </a>
        </div>

        <div class="w-full sm:max-w-lg mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900">Kody odzyskiwania 2FA</h2>
                <p class="text-sm text-gray-600 mt-2">
                    Zapisz te kody w bezpiecznym miejscu. Każdy kod można użyć tylko raz.
                </p>
            </div>

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded no-print">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded no-print">
                    {{ session('warning') }}
                </div>
            @endif

            <!-- Recovery Codes Display -->
            <div class="bg-gray-50 p-6 rounded-lg border-2 border-dashed border-gray-300">
                <div class="grid grid-cols-2 gap-3">
                    @foreach($recoveryCodes as $code)
                        <div class="bg-white p-3 rounded border font-mono text-sm text-center">
                            {{ $code }}
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Warning Box -->
            <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex">
                    <div class="text-red-400 mr-3">⚠️</div>
                    <div>
                        <h4 class="text-red-800 font-semibold mb-2">Ważne informacje bezpieczeństwa:</h4>
                        <ul class="text-sm text-red-700 space-y-1">
                            <li>• Każdy kod można użyć tylko <strong>jeden raz</strong></li>
                            <li>• Przechowuj je w <strong>bezpiecznym miejscu</strong> (nie na komputerze)</li>
                            <li>• Użyj ich tylko gdy <strong>stracisz dostęp</strong> do aplikacji uwierzytelniającej</li>
                            <li>• Po użyciu wszystkich kodów, <strong>wygeneruj nowe</strong></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row gap-3 no-print">
                <button onclick="window.print()" 
                        class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    🖨️ Wydrukuj kody
                </button>
                
                <button onclick="copyAllCodes()" 
                        id="copyButton"
                        class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    📋 Skopiuj wszystkie
                </button>
            </div>

            <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}" class="mt-4 no-print">
                @csrf
                <button type="submit" 
                        onclick="return confirm('Czy na pewno chcesz wygenerować nowe kody? Stare kody przestaną działać.')"
                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    🔄 Wygeneruj nowe kody
                </button>
            </form>

            <!-- Navigation -->
            <div class="mt-6 flex justify-between no-print">
                <a href="/admin" 
                   class="text-sm text-gray-600 hover:text-gray-900 underline">
                    ← Przejdź do panelu
                </a>
                <a href="{{ route('two-factor.setup') }}" 
                   class="text-sm text-indigo-600 hover:text-indigo-800 underline">
                    Ustawienia 2FA →
                </a>
            </div>

            <!-- User Info for Print -->
            <div class="mt-8 pt-4 border-t border-gray-200 text-sm text-gray-500 print-only" style="display: none;">
                <p><strong>Użytkownik:</strong> {{ auth()->user()->name }}</p>
                <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                <p><strong>Data wygenerowania:</strong> {{ now()->format('d.m.Y H:i') }}</p>
            </div>
        </div>
    </div>

    <script>
        function copyAllCodes() {
            const codes = @json($recoveryCodes);
            const codeText = codes.join('\n');
            
            navigator.clipboard.writeText(codeText).then(function() {
                const button = document.getElementById('copyButton');
                const originalText = button.innerHTML;
                
                button.innerHTML = '✅ Skopiowane!';
                button.className = button.className.replace('bg-green-600 hover:bg-green-700', 'bg-gray-600 hover:bg-gray-700');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.className = button.className.replace('bg-gray-600 hover:bg-gray-700', 'bg-green-600 hover:bg-green-700');
                }, 2000);
            }).catch(function() {
                alert('Nie udało się skopiować kodów. Skopiuj je ręcznie.');
            });
        }

        // Show user info when printing
        window.addEventListener('beforeprint', function() {
            document.querySelector('.print-only').style.display = 'block';
        });

        window.addEventListener('afterprint', function() {
            document.querySelector('.print-only').style.display = 'none';
        });
    </script>
</body>
</html>