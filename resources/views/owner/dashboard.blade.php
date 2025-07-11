<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Panel właściciela</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>
        body { font-family: 'Figtree', sans-serif; }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-100">
    <!-- 2FA Reminder -->
    @include('components.two-factor-reminder')

    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <img src="{{ asset('graphics/logo.svg') }}" alt="eZM2" class="w-10 h-10 mr-3" 
                             onerror="this.onerror=null; this.src='{{ asset('graphics/logo_size_1.png') }}';" />
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900">Panel właściciela</h1>
                            <p class="text-sm text-gray-600">Witaj, {{ $user->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('profile.show') }}" class="text-gray-600 hover:text-gray-900">
                            ⚙️ Ustawienia
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900">
                                🚪 Wyloguj
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-2">Witaj w eZM2!</h2>
                    <p class="text-gray-600">
                        Tu możesz zarządzać swoim mieszkaniem, sprawdzać opłaty i komunikować się z zarządcą.
                    </p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5 text-center">
                        <div class="text-3xl mb-2">🏠</div>
                        <h3 class="text-lg font-medium text-gray-900">Moje mieszkanie</h3>
                        <p class="text-sm text-gray-600 mb-3">Informacje o mieszkaniu</p>
                        <a href="#" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Zobacz szczegóły
                        </a>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5 text-center">
                        <div class="text-3xl mb-2">💰</div>
                        <h3 class="text-lg font-medium text-gray-900">Opłaty</h3>
                        <p class="text-sm text-gray-600 mb-3">Historia i bieżące opłaty</p>
                        <a href="#" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            Sprawdź opłaty
                        </a>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5 text-center">
                        <div class="text-3xl mb-2">💧</div>
                        <h3 class="text-lg font-medium text-gray-900">Zużycie wody</h3>
                        <p class="text-sm text-gray-600 mb-3">Odczyty i historia</p>
                        <a href="#" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Zobacz odczyty
                        </a>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5 text-center">
                        <div class="text-3xl mb-2">📄</div>
                        <h3 class="text-lg font-medium text-gray-900">Dokumenty</h3>
                        <p class="text-sm text-gray-600 mb-3">Faktury i uchwały</p>
                        <a href="#" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                            Przeglądaj
                        </a>
                    </div>
                </div>
            </div>

            <!-- My Apartments -->
            @if($apartments && count($apartments) > 0)
            <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Moje mieszkania</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($apartments as $apartment)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-900">
                                        Mieszkanie {{ $apartment->full_number }}
                                    </h4>
                                    <p class="text-sm text-gray-600">
                                        {{ $apartment->community->name }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        Powierzchnia: {{ $apartment->area }} m²
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Aktywne
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="text-yellow-400 mr-3">⚠️</div>
                    <div>
                        <h4 class="text-yellow-800 font-medium">Brak przypisanych mieszkań</h4>
                        <p class="text-yellow-700 text-sm mt-1">
                            Skontaktuj się z zarządcą aby przypisać Twoje konto do mieszkania.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Activity -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ostatnia aktywność</h3>
                    <div class="text-sm text-gray-600">
                        <p>Brak ostatniej aktywności do wyświetlenia.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>