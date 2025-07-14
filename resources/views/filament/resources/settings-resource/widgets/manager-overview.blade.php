{{-- resources/views/filament/resources/settings-resource/widgets/manager-overview.blade.php --}}

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Status konfiguracji
        </x-slot>

        <div class="space-y-4">
            @if ($is_configured)
                <div class="flex items-center space-x-2">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-success-500" />
                    <span class="text-sm font-medium text-success-700 dark:text-success-400">
                        Aplikacja jest skonfigurowana i gotowa do użycia
                    </span>
                </div>

                <div class="bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg p-4">
                    <p class="text-sm text-success-800 dark:text-success-200">
                        Zarządca: <strong>{{ $manager_data['name'] ?: 'Nie ustawiono' }}</strong>
                    </p>
                    <p class="text-xs text-success-600 dark:text-success-300 mt-1">
                        Wszystkie podstawowe ustawienia zostały skonfigurowane.
                    </p>
                </div>
            @else
                <div class="flex items-center space-x-2">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-warning-500" />
                    <span class="text-sm font-medium text-warning-700 dark:text-warning-400">
                        Wymagana konfiguracja
                    </span>
                </div>

                <div class="bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-lg p-4">
                    <p class="text-sm text-warning-800 dark:text-warning-200">
                        Skonfiguruj podstawowe ustawienia, aby móc w pełni korzystać z funkcji aplikacji.
                    </p>
                    <p class="text-xs text-warning-600 dark:text-warning-300 mt-1">
                        Kliknij przycisk "Konfiguruj ustawienia" powyżej.
                    </p>
                </div>
            @endif

            @if (!$is_initialized)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-center space-x-2 mb-2">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500" />
                        <span class="text-sm font-medium text-blue-700 dark:text-blue-400">
                            Pierwsze uruchomienie
                        </span>
                    </div>
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        To jest pierwsze uruchomienie aplikacji. Skonfiguruj podstawowe ustawienia, aby rozpocząć pracę.
                    </p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>