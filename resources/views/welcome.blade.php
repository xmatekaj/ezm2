<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EZM - System Zarządzania Wspólnotami</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Hero Section -->
    <div class="min-h-screen bg-gradient-to-br from-blue-600 to-purple-700">
        <!-- Navigation -->
        <nav class="bg-white/10 backdrop-blur-lg border-b border-white/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-white">EZM</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-white hover:text-blue-200 transition-colors">
                            Zaloguj się
                        </a>
                        <a href="{{ route('register.form') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition-colors">
                            Zarejestruj się
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
                    System Zarządzania<br>
                    <span class="text-blue-200">Wspólnotami</span>
                </h1>
                <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                    Nowoczesne rozwiązanie do zarządzania wspólnotami mieszkaniowymi.
                    Rozliczenia, wodomierze, finanse - wszystko w jednym miejscu.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register.form') }}" class="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-blue-50 transition-colors">
                        Zarejestruj się teraz
                    </a>
                    <a href="{{ route('login') }}" class="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                        Masz już konto?
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Dlaczego warto wybrać EZM?
                </h2>
                <p class="text-lg text-gray-600">
                    Kompletne rozwiązanie dla mieszkańców i zarządców
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        Łatwe rozliczenia
                    </h3>
                    <p class="text-gray-600">
                        Automatyczne naliczanie opłat i generowanie rozliczeń dla mieszkańców
                    </p>
                </div>

                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        Monitoring wodomierzy
                    </h3>
                    <p class="text-gray-600">
                        Śledzenie zużycia wody i automatyczne naliczanie opłat wodnych
                    </p>
                </div>

                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        Bezpieczeństwo
                    </h3>
                    <p class="text-gray-600">
                        Dwustopniowa weryfikacja i bezpieczne przechowywanie danych
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration CTA -->
    <div class="py-20 bg-blue-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">
                Gotowy na start?
            </h2>
            <p class="text-xl text-blue-100 mb-8">
                Zarejestruj się już teraz i zacznij korzystać z systemu
            </p>
            <a href="{{ route('register.form') }}" class="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-blue-50 transition-colors">
                Rozpocznij rejestrację
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h3 class="text-2xl font-bold mb-4">EZM</h3>
                <p class="text-gray-400">
                    System Zarządzania Wspólnotami © {{ date('Y') }}
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
