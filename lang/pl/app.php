<?php
// lang/pl/app.php
return [
	'action_titles' => [
		'view' => [
			'person' => 'Podgląd osoby',
			'community' => 'Podgląd wspólnoty',
			'apartment' => 'Podgląd lokalu',
            'water_meter' => 'Podgląd wodomierza',

		],
		'edit' => [
			'person' => 'Edycja osoby',
			'community' => 'Edycja wspólnoty',
			'apartment' => 'Edycja lokalu',
            'water_meter' => 'Edycja wodomierza',
		],
		'create' => [
			'person' => 'Utwórz osobę',
			'community' => 'Utwórz wspólnotę',
			'apartment' => 'Utwórz lokal',
            'water_meter' => 'Utwórz wodomierz',
		],
	],
    'navigation' => [
        'communities' => 'Wspólnoty',
        'apartments' => 'Lokale',
        'people' => 'Osoby',
        'utilities' => 'Media',
        'financial_transactions' => 'Transakcje finansowe',
        'water_meters' => 'Wodomierze',
        'water_readings' => 'Odczyty wodomierzy',
        'prices' => 'Cennik',
        'bank_accounts' => 'Konta bankowe',
        'occupancy' => 'Zaludnienie',
        'import_jobs' => 'Import danych',
        'settings' => 'Ustawienia',
        'users' => 'Użytkownicy',

    ],

    'groups' => [
        'management' => 'Zarządzanie',
        'finance' => 'Finanse',
        'utilities' => 'Media',
        'system' => 'System',
    ],

    'communities' => [
        'singular' => 'Wspólnota',
        'plural' => 'Wspólnoty',
        'name' => 'Nazwa',
        'full_name' => 'Pełna nazwa',
        'internal_code' => 'Wewnętrzny kod wspólnoty',
        'address_street' => 'Ulica',
        'address_postal_code' => 'Kod pocztowy',
        'address_city' => 'Miasto',
        'address_state' => 'Województwo',
        'regon' => 'REGON',
        'tax_id' => 'NIP',
        'land_mortgage_register' => 'Księga Wieczysta',
        'common_area_size' => 'Powierzchnia części wspólnych (m²)',
        'apartments_area' => 'Powierzchnia lokali (m²)',
        'apartment_count' => 'Liczba lokali',
        'staircase_count' => 'Liczba klatek',
        'has_elevator' => 'Winda',
        'residential_water_meters' => 'Wodomierze mieszkaniowe',
        'main_water_meters' => 'Wodomierze główne',
        'is_active' => 'Aktywna',
        'color' => 'Kolor',
        'full_address' => 'Pełny adres',
        'total_area' => 'Powierzchnia (m²)',
        'basic_info_description' => 'Podstawowe informacje o wspólnocie mieszkaniowej',
        'address_info_description' => 'Lokalizacja i adres wspólnoty',
        'identifiers_description' => 'Identyfikatory prawne (opcjonalne)',
        'technical_params_description' => 'Parametry techniczne budynku (opcjonalne)',
        'regon_help' => 'REGON nie jest wymagany - można wprowadzić później',
        'nip_help' => 'NIP nie jest wymagany - można wprowadzić później',

    ],

    'settings' => [
        'singular' => 'Ustawienie',
        'plural' => 'Ustawienia',
        'manager_name' => 'Nazwa zarządcy',
        'manager_address_street' => 'Ulica zarządcy',
        'manager_address_postal_code' => 'Kod pocztowy zarządcy',
        'manager_address_city' => 'Miasto zarządcy',
        'app_initialized' => 'Aplikacja zainicjowana',
        'manager_section' => 'Zarządca',
    ],

    'apartments' => [
        'singular' => 'Lokal',
        'plural' => 'Lokale',
        'building_number' => 'Numer budynku',
        'apartment_number' => 'Numer lokalu',
        'area' => 'Powierzchnia (m²)',
        'basement_area' => 'Powierzchnia piwnicy (m²)',
        'storage_area' => 'Powierzchnia komórki (m²)',
        'common_area_share' => 'Udział w częściach wspólnych (%)',
        'floor' => 'Piętro',
        'elevator_fee_coefficient' => 'Współczynnik opłaty windowej',
        'has_basement' => 'Posiada piwnicę',
        'has_storage' => 'Posiada komórkę',
        'is_owned' => 'Własnościowy',
        'is_commercial' => 'Komercyjny',
        'full_number' => 'Pełny numer',
        'types' => [
            'residential' => 'Mieszkalny',
            'commercial' => 'Komercyjny',
            'mixed' => 'Mieszany'
        ],
        'code' => 'Kod lokalu',
        'intercom_code' => 'Kod domofonu',
        'land_mortgage_register' => 'Księga Wieczysta',
        'primary_owner' => 'Właściciel główny',
        'basement_area_conditional' => 'Powierzchnia piwnicy (m²)',
        'storage_area_conditional' => 'Powierzchnia komórki (m²)',
        'owners' => 'Właściciele',
        'basement_area_conditional' => 'Powierzchnia piwnicy (m²)',
        'storage_area_conditional' => 'Powierzchnia komórki (m²)',
        'floor_display' => 'Piętro',
        'basic_info_description' => 'Podstawowe informacje o lokalu',
        'surfaces_description' => 'Powierzchnie i udziały w częściach wspólnych',
        'additional_description' => 'Dodatkowe udogodnienia i właściciele',
    ],

    'financial_transactions' => [
        'singular' => 'Transakcja finansowa',
        'plural' => 'Transakcje finansowe',
        'amount' => 'Kwota (PLN)',
        'is_credit' => 'Wpłata',
        'booking_date' => 'Data księgowania',
        'transaction_number' => 'Numer transakcji',
        'counterparty_details' => 'Dane kontrahenta',
        'title' => 'Tytuł przelewu',
        'additional_info' => 'Dodatkowe informacje',
        'notes' => 'Notatki',
        'type' => 'Typ',
        'formatted_amount' => 'Kwota',
        'types' => [
            'credit' => 'Wpłata',
            'debit' => 'Wydatek',
        ],
    ],

    'bank_accounts' => [
        'singular' => 'Konto bankowe',
        'plural' => 'Konta bankowe',
        'account_number' => 'Numer konta',
        'formatted_account_number' => 'Numer konta',
        'swift' => 'SWIFT/BIC',
        'bank_name' => 'Nazwa banku',
        'address_street' => 'Ulica banku',
        'address_postal_code' => 'Kod pocztowy banku',
        'address_city' => 'Miasto banku',
        'full_bank_address' => 'Adres banku',
        'balance' => 'Saldo (PLN)',
        'is_active' => 'Aktywne',
    ],

    'prices' => [
        'singular' => 'Cennik',
        'plural' => 'Cenniki',
        'change_date' => 'Data obowiązywania',
        'water_sewage_price' => 'Woda i ścieki (PLN/m³)',
        'garbage_price' => 'Opłata za odpady (PLN)',
        'management_fee' => 'Opłata za zarządzanie (PLN)',
        'renovation_fund' => 'Fundusz remontowy (PLN)',
        'loan_fund' => 'Fundusz kredytowy (PLN)',
        'central_heating_advance' => 'Zaliczka na c.o. (PLN)',
        'total_monthly_fee' => 'Suma miesięczna (PLN)',
    ],

    'occupancy' => [
        'singular' => 'Zaludnienie',
        'plural' => 'Zaludnienie',
        'number_of_occupants' => 'Liczba mieszkańców',
        'change_date' => 'Data zmiany',
        'occupancy_change' => 'Zmiana',
    ],

    'sections' => [
        'basic_information' => 'Podstawowe informacje',
        'address' => 'Adres',
        'identifiers' => 'Identyfikatory',
        'manager' => 'Zarządca', // Kept for backward compatibility
        'technical_parameters' => 'Parametry techniczne',
        'surfaces' => 'Powierzchnie',
        'additional' => 'Dodatkowe',
        'transaction_details' => 'Szczegóły transakcji',
        'connections' => 'Powiązania',
        'alarms' => 'Alarmy',
        'assignment' => 'Przypisanie',
        'water_meter' => 'Wodomierz',
        'transmitter' => 'Nadajnik',
        'fixed_fees' => 'Opłaty stałe (PLN)',
        'variable_fees' => 'Opłaty zmienne (PLN/m³)',
        'occupancy_change' => 'Zmiana zaludnienia',
        'basic_information' => 'Podstawowe informacje',
        'address' => 'Adres',
        'identifiers' => 'Identyfikatory',
        'technical_parameters' => 'Parametry techniczne',
        'surfaces' => 'Powierzchnie',
    ],
    'filters' => [
        'active' => 'Aktywny',
        'inactive' => 'Nieaktywny',
        'owned' => 'Własnościowy',
        'commercial' => 'Komercyjny',
        'with_basement' => 'Z piwnicą',
        'with_storage' => 'Z komórką',
        'has_spouse' => 'Ma małżonka',
        'with_apartments' => 'Ma lokale',
        'all' => 'Wszystkie',
        'yes' => 'Tak',
        'no' => 'Nie',
    ],

    'common' => [
		'community' => 'Wspólnota',
		'apartment' => 'Lokal',
		'person' => 'Osoba',
		'created_at' => 'Utworzono',
		'updated_at' => 'Zaktualizowano',
		'active' => 'Aktywny',
		'inactive' => 'Nieaktywny',
		'yes' => 'Tak',
		'no' => 'Nie',
		'name' => 'Nazwa',
		'save' => 'Zapisz',
		'cancel' => 'Anuluj',
		'delete' => 'Usuń',
		'edit' => 'Edytuj',
		'view' => 'Podgląd',
		'create' => 'Utwórz',
		'search' => 'Szukaj',
		'filter' => 'Filtruj',
		'actions' => 'Akcje',
		'status' => 'Status',
		'date' => 'Data',
		'amount' => 'Kwota',
		'type' => 'Typ',
		'description' => 'Opis',
		'notes' => 'Notatki',
		'address' => 'Adres',
		'phone' => 'Telefon',
		'email' => 'Email',
		'select_option' => 'Wybierz opcję',
		'no_results' => 'Brak wyników',
		'loading' => 'Ładowanie...',
		'success' => 'Sukces',
		'error' => 'Błąd',
		'warning' => 'Ostrzeżenie',
		'info' => 'Informacja',
        'import' => 'Importuj',
		'download' => 'Pobierz',
        'download_csv_template' => 'Pobierz szablon CSV',
        'download_template' => 'Pobierz szablon CSV',
		'csv_file' => 'Plik CSV',
		'import_function' => 'Import funkcja',
		'import_will_be_implemented_soon' => 'Import będzie zaimplementowany wkrótce',
	],
    'people' => [
        'singular' => 'Osoba',
        'plural' => 'Osoby',
        'first_name' => 'Imię',
        'last_name' => 'Nazwisko',
        'full_name' => 'Imię i nazwisko',
        'email' => 'Adres email',
        'phone' => 'Telefon',
        'correspondence_address_street' => 'Ulica (adres korespondencyjny)',
        'correspondence_address_postal_code' => 'Kod pocztowy',
        'correspondence_address_city' => 'Miasto',
        'full_address' => 'Pełny adres',
        'is_active' => 'Aktywny',
        'notes' => 'Notatki',
        'ownership_share' => 'Udział własnościowy (%)',
        'spouse' => 'Małżonek/Współmałżonek',
        'spouse_id' => 'Małżonek',
        'primary_apartment' => 'Główny lokal',
        'apartments_count' => 'Liczba lokali',
        'basic_info_description' => 'Podstawowe dane osobowe',
        'address_description' => 'Adres do korespondencji',
        'additional_description' => 'Dodatkowe informacje i powiązania',
        'contact_info' => 'Dane kontaktowe',
        'personal_data' => 'Dane osobowe',
    ],

    'statuses' => [
        'active' => 'Aktywny',
        'inactive' => 'Nieaktywny',
        'pending' => 'Oczekujący',
        'approved' => 'Zatwierdzony',
        'rejected' => 'Odrzucony',
        'completed' => 'Zakończony',
        'in_progress' => 'W trakcie',
        'cancelled' => 'Anulowany',
        'expired' => 'Wygasły',
        'verified' => 'Zweryfikowany',
        'unverified' => 'Niezweryfikowany',
    ],

    'actions' => [
        'create' => 'Utwórz',
        'edit' => 'Edytuj',
        'view' => 'Podgląd',
        'delete' => 'Usuń',
        'save' => 'Zapisz',
        'cancel' => 'Anuluj',
        'back' => 'Wstecz',
        'next' => 'Dalej',
        'previous' => 'Poprzedni',
        'submit' => 'Wyślij',
        'reset' => 'Resetuj',
        'refresh' => 'Odśwież',
        'export' => 'Eksportuj',
        'import' => 'Importuj',
        'download' => 'Pobierz',
        'upload' => 'Prześlij',
        'search' => 'Szukaj',
        'filter' => 'Filtruj',
        'sort' => 'Sortuj',
        'duplicate' => 'Duplikuj',
        'archive' => 'Archiwizuj',
        'restore' => 'Przywróć',
        'activate' => 'Aktywuj',
        'deactivate' => 'Dezaktywuj',
        'approve' => 'Zatwierdź',
        'reject' => 'Odrzuć',
        'send' => 'Wyślij',
        'resend' => 'Wyślij ponownie',
        'verify' => 'Zweryfikuj',
        'confirm' => 'Potwierdź',
    ],


'import' => [
    // General import messages
    'validation_failed' => 'Walidacja nie powiodła się',
    'row_processing_error' => 'Błąd przetwarzania wiersza',
    'row_error' => 'Wiersz :row: :message',
    'file_not_found' => 'Nie znaleziono pliku',
    'file_read_error' => 'Błąd odczytu pliku',
    'invalid_file_format' => 'Nieprawidłowy format pliku',
    'encoding_conversion_failed' => 'Błąd konwersji kodowania',
    'invalid_import_type' => 'Nieprawidłowy typ importu: :type',

    // Import statistics
    'import_completed' => 'Import zakończony pomyślnie',
    'import_failed' => 'Import nie powiódł się',
    'created_count' => 'Utworzono: :count rekordów',
    'updated_count' => 'Zaktualizowano: :count rekordów',
    'error_count' => 'Błędy: :count rekordów',
    'skipped_count' => 'Pominięto: :count rekordów',
    'total_count' => 'Łącznie: :count rekordów',
    'statistics' => 'Statystyki',

    // CSV parsing
    'delimiter_detected' => 'Wykryto separator: :delimiter',
    'encoding_detected' => 'Wykryto kodowanie: :encoding',
    'decimal_separator_info' => 'Separator dziesiętny: :separator',
    'preview_rows' => 'Podgląd pierwszych wierszy',
    'column_count' => 'Liczba kolumn: :count',
    'row_count' => 'Liczba wierszy: :count',

    // File validation
    'file_validation_passed' => 'Walidacja pliku zakończona pomyślnie',
    'file_validation_failed' => 'Walidacja pliku nie powiodła się',
    'missing_required_column' => 'Brakuje wymaganej kolumny: :column',
    'invalid_data_in_column' => 'Nieprawidłowe dane w kolumnie :column',
    'inconsistent_column_count' => 'Niekonsystentna liczba kolumn w wierszu :row',
    'empty_file' => 'Plik jest pusty',
    'no_data_rows' => 'Brak wierszy z danymi',

    // Apartment Import
    'apartment' => [
        'import_apartments' => 'Importuj lokale',
        'import_description' => 'Importuj lokale z pliku CSV do wybranej wspólnoty',
        'select_community' => 'Wybierz wspólnotę',
        'community_help' => 'Wybierz wspólnotę, do której chcesz zaimportować lokale',
        'community_required' => 'Wspólnota jest wymagana do importu lokali',
        'community_not_found' => 'Nie znaleziono wspólnoty o podanym ID',
        'community' => 'Wspólnota',
        'unknown_community' => 'Nieznana wspólnota',
        'import_completed' => 'Import lokali zakończony pomyślnie!',
        'import_success' => 'Import zakończony',
        'import_error_message' => 'Wystąpił błąd podczas importu lokali',
        'required_columns_description' => 'Sprawdź wymagane i opcjonalne kolumny dla importu lokali',
        'duplicate_apartment' => 'Lokal :number już istnieje w budynku :building',
        'no_building' => 'brak numeru budynku',
        'creation_failed' => 'Nie udało się utworzyć lokalu',
        'invalid_apartment_type' => 'Nieprawidłowy typ lokalu: :type',
        'invalid_floor' => 'Nieprawidłowe piętro: :floor',
        'invalid_area' => 'Nieprawidłowa powierzchnia: :area',
        'missing_apartment_number' => 'Brakuje numeru lokalu',
        'apartment_number_too_long' => 'Numer lokalu jest za długi (max 10 znaków)',
        'building_number_too_long' => 'Numer budynku jest za długi (max 10 znaków)',
        'area_too_large' => 'Powierzchnia jest za duża (max 9999.99 m²)',
        'negative_area' => 'Powierzchnia nie może być ujemna',
        'invalid_coefficient' => 'Nieprawidłowy współczynnik windy: :coefficient',

        // Example values
        'example_apartment_number' => 'Przykłady numerów lokali',
        'example_area' => 'Przykłady powierzchni',
        'example_floor' => 'Przykłady pięter',
        'example_boolean' => 'Wartości tak/nie',
        'example_type' => 'Typy lokali',
    ],

    // Form labels and settings
    'form' => [
        'import_settings' => 'Ustawienia importu',
        'file_selection' => 'Wybór pliku',
        'format_settings' => 'Ustawienia formatu',
        'preview_section' => 'Podgląd pliku',
        'select_file' => 'Wybierz plik CSV',
        'file_help' => 'Obsługiwane formaty: CSV, TXT (maksymalnie 10MB)',

        // Delimiter options
        'delimiter' => 'Separator kolumn',
        'delimiter_help' => 'Wybierz separator używany między kolumnami w pliku',
        'comma_standard' => 'Przecinek (,) - standard międzynarodowy',
        'semicolon_polish' => 'Średnik (;) - standard polski/europejski',
        'tab_excel' => 'Tabulator (Tab) - eksport z Excela',
        'pipe_custom' => 'Pionowa kreska (|) - format niestandardowy',
        'auto_detect' => 'Automatyczne wykrywanie',

        // Quote options
        'quote_handling' => 'Obsługa cudzysłowów',
        'quote_help' => 'Jak obsługiwać wartości w cudzysłowach',
        'quotes_standard' => 'Standardowe ("wartość")',
        'quotes_minimal' => 'Minimalne (tylko gdy potrzebne)',
        'quotes_none' => 'Bez cudzysłowów',
        'quotes_single' => 'Pojedyncze (\' zamiast ")',

        // Decimal options
        'decimal_separator' => 'Separator dziesiętny',
        'decimal_help' => 'Format liczb dziesiętnych w pliku',
        'decimal_dot' => 'Kropka (.) - format angielski',
        'decimal_comma' => 'Przecinek (,) - format polski',

        // Encoding options
        'encoding' => 'Kodowanie pliku',
        'encoding_help' => 'Kodowanie znaków używane w pliku',
        'utf8_universal' => 'UTF-8 (uniwersalne)',
        'iso_central_europe' => 'ISO-8859-2 (Europa Środkowa)',
        'windows_polish' => 'Windows-1250 (Polski Windows)',
        'iso_western_europe' => 'ISO-8859-1 (Europa Zachodnia)',

        // Other options
        'skip_header' => 'Pomiń nagłówki',
        'skip_header_help' => 'Pierwszy wiersz zawiera nazwy kolumn',
        'trim_whitespace' => 'Usuń spacje',
        'trim_help' => 'Automatycznie usuń spacje z początku i końca wartości',
        'skip_empty_rows' => 'Pomiń puste wiersze',
        'skip_empty_help' => 'Ignoruj całkowicie puste wiersze',

        // Simple names for delimiters
        'comma' => 'przecinek',
        'semicolon' => 'średnik',
        'tab' => 'tabulator',
        'pipe' => 'pionowa kreska',
        'custom' => 'niestandardowy',

        // Format descriptions
        'polish_format' => 'format polski (,)',
        'english_format' => 'format angielski (.)',
        'decimal_format' => 'Format liczb',
        'settings_used' => 'Użyte ustawienia',
        'file_info' => 'Informacje o pliku',

        // Preview messages
        'preview_placeholder' => 'Wybierz plik CSV aby zobaczyć podgląd struktury...',
        'preview_error' => 'Błąd podczas generowania podglądu',
        'preview_note' => 'Wyświetlono pierwsze 3 wiersze z :total łącznie',
        'no_data_preview' => 'Brak danych do wyświetlenia',

        // Actions
        'start_import' => 'Rozpocznij import',
        'cancel' => 'Anuluj',
    ],

    // Column names (Polish to English mapping)
    'columns' => [
        // Apartment columns
        'numer_lokalu' => 'Numer lokalu',
        'numer_budynku' => 'Numer budynku',
        'powierzchnia' => 'Powierzchnia',
        'piętro' => 'Piętro',
        'współczynnik_windy' => 'Współczynnik windy',
        'ma_piwnicę' => 'Ma piwnicę',
        'ma_komórkę' => 'Ma komórkę',
        'typ_lokalu' => 'Typ lokalu',
        'kod' => 'Kod',
        'domofon' => 'Kod domofonu',
        'księga_wieczysta' => 'Księga wieczysta',
        'udział_części_wspólnych' => 'Udział części wspólnych',
        'powierzchnia_piwnicy' => 'Powierzchnia piwnicy',
        'powierzchnia_komórki' => 'Powierzchnia komórki',
        'opis_przeznaczenia' => 'Opis przeznaczenia',
        'osobne_wejście' => 'Osobne wejście',
        'powierzchnia_użytkowa' => 'Powierzchnia użytkowa',

        // Community columns
        'nazwa' => 'Nazwa',
        'pełna_nazwa' => 'Pełna nazwa',
        'kod_wewnętrzny' => 'Kod wewnętrzny',
        'ulica' => 'Ulica',
        'kod_pocztowy' => 'Kod pocztowy',
        'miasto' => 'Miasto',
        'województwo' => 'Województwo',
        'regon' => 'REGON',
        'nip' => 'NIP',
    ],

    // Help messages
    'help' => [
        'csv_format' => 'Plik CSV powinien zawierać następujące kolumny:',
        'delimiter_info' => 'Obsługiwane separatory: przecinek (,), średnik (;), tabulator (Tab)',
        'encoding_info' => 'Obsługiwane kodowania: UTF-8, Windows-1250, ISO-8859-2',
        'decimal_info' => 'Separatory dziesiętne: kropka (.) lub przecinek (,)',
        'boolean_values' => 'Wartości logiczne: tak/nie, true/false, 1/0, x/-',
        'apartment_types' => 'Typy lokali: mieszkaniowy, komercyjny, mieszany, garaż, piwnica',
        'required_fields' => 'Wymagane pola',
        'optional_fields' => 'Pola opcjonalne',
        'example_values' => 'Przykładowe wartości',
        'download_template' => 'Pobierz szablon CSV',
        'preview_before_import' => 'Zalecamy podgląd pliku przed importem',
    ],

    // Status messages
    'status' => [
        'processing' => 'Przetwarzanie...',
        'completed' => 'Zakończono',
        'failed' => 'Niepowodzenie',
        'validating' => 'Walidacja...',
        'reading_file' => 'Odczyt pliku...',
        'parsing_csv' => 'Analiza CSV...',
        'converting_data' => 'Konwersja danych...',
        'creating_records' => 'Tworzenie rekordów...',
        'finalizing' => 'Finalizacja...',
    ],
],

/*
=============================================================================
USAGE IN YOUR CODE:
=============================================================================

Instead of:
__('import.apartment.community_required')

Use:
__('app.import.apartment.community_required')

Instead of:
__('import.form.delimiter')

Use:
__('app.import.form.delimiter')

Examples:
- __('app.import.apartment.import_apartments')
- __('app.import.form.select_file')
- __('app.import.validation_failed')
- __('app.import.statistics')
- __('app.import.help.required_fields')

=============================================================================
UPDATE YOUR IMPORT CODE:
=============================================================================

In the ImportAction and ImportManager files, change:

// OLD:
__('import.apartment.community_required')
__('import.validation_failed')
__('import.form.delimiter')

// NEW:
__('app.import.apartment.community_required')
__('app.import.validation_failed')
__('app.import.form.delimiter')

=============================================================================
*/
];
