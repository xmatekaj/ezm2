<?php
// routes/web.php

use App\Http\Controllers\Auth\OwnerRegistrationController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Api\TerritorialApiController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// Dashboard routes
Route::middleware(['auth', 'two-factor'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Landing page
Route::get('/', function () {
    return view('welcome'); // Landing page with registration info
})->name('welcome');

// Owner Registration Routes (everyone uses the same registration)
Route::prefix('register')->name('register.')->group(function () {
    Route::get('/', [RegistrationController::class, 'showRegistrationForm'])->name('form');
    Route::post('/', [RegistrationController::class, 'initiateRegistration'])->name('initiate');
    Route::get('/complete/{token}', [RegistrationController::class, 'showCompleteForm'])->name('complete');
    Route::post('/complete/{token}', [RegistrationController::class, 'completeRegistration'])->name('complete.submit');
});

// Authentication Routes
Route::get('/login', [CustomLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [CustomLoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [CustomLoginController::class, 'logout'])->name('logout');

// API Routes for Territorial Data
Route::prefix('api')->group(function () {
    Route::get('/voivodeships', [TerritorialApiController::class, 'getVoivodeships']);
    Route::get('/cities/{voivodeship}', [TerritorialApiController::class, 'getCities']);
    Route::get('/streets/{voivodeship}/{city}', [TerritorialApiController::class, 'getStreets']);
});

// 2FA reminder dismissal
Route::middleware(['auth'])->group(function () {
    Route::post('/dismiss-2fa-reminder', [DashboardController::class, 'dismiss2FAReminder'])->name('dismiss-2fa-reminder');
});

// Profile routes
Route::middleware(['auth', 'two-factor'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});



// Owner routes - only for owners
Route::middleware(['auth', 'two-factor', 'role:owner'])->group(function () {
    Route::get('/owner/dashboard', [DashboardController::class, 'ownerDashboard'])->name('owner.dashboard');
});

// 2FA Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::post('/two-factor/email', [TwoFactorController::class, 'sendEmailCode'])->name('two-factor.email');

    Route::get('/two-factor/setup', [TwoFactorSetupController::class, 'show'])->name('two-factor.setup');
    Route::post('/two-factor/enable', [TwoFactorSetupController::class, 'enable'])->name('two-factor.enable');
    Route::get('/two-factor/recovery-codes', [TwoFactorSetupController::class, 'showRecoveryCodes'])->name('two-factor.recovery-codes');
    Route::post('/two-factor/regenerate-recovery-codes', [TwoFactorSetupController::class, 'regenerateRecoveryCodes'])->name('two-factor.regenerate-recovery-codes');
    Route::post('/two-factor/disable', [TwoFactorSetupController::class, 'disable'])->name('two-factor.disable');
});

Route::get('/debug-storage-structure', function() {
    $storageBasePath = storage_path('app');

    $structure = [];
    $recentFiles = [];

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($storageBasePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $relativePath = str_replace($storageBasePath . '/', '', $file->getPathname());

            if ($file->isDir()) {
                $structure['directories'][] = $relativePath;
            } else {
                $structure['files'][] = [
                    'path' => $relativePath,
                    'full_path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                    'extension' => $file->getExtension()
                ];

                // Collect recent files (last 10 minutes)
                if ($file->getMTime() > (time() - 600)) {
                    $recentFiles[] = [
                        'path' => $relativePath,
                        'full_path' => $file->getPathname(),
                        'size' => $file->getSize(),
                        'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                        'extension' => $file->getExtension()
                    ];
                }
            }
        }

        // Sort recent files by modification time
        usort($recentFiles, function($a, $b) {
            return $b['modified'] <=> $a['modified'];
        });

    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }

    return response()->json([
        'storage_base_path' => $storageBasePath,
        'directory_structure' => $structure['directories'] ?? [],
        'total_files' => count($structure['files'] ?? []),
        'recent_files' => array_slice($recentFiles, 0, 20), // Show 20 most recent
        'storage_disks' => array_keys(config('filesystems.disks')),
        'local_disk_config' => config('filesystems.disks.local')
    ]);
});

// Add this to routes/web.php to test file uploads independently

Route::get('/test-upload', function() {
    return view('test-upload');
});

Route::post('/test-upload', function(\Illuminate\Http\Request $request) {
    try {
        \Log::info('Test upload received', [
            'files' => $request->allFiles(),
            'has_file' => $request->hasFile('test_file')
        ]);

        if ($request->hasFile('test_file')) {
            $file = $request->file('test_file');

            \Log::info('File details', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'temp_path' => $file->getPathname(),
                'is_valid' => $file->isValid()
            ]);

            // Store the file
            $path = $file->store('test-uploads', 'local');

            \Log::info('File stored', [
                'stored_path' => $path,
                'full_path' => Storage::disk('local')->path($path),
                'storage_exists' => Storage::disk('local')->exists($path)
            ]);

            return response()->json([
                'status' => 'success',
                'stored_path' => $path,
                'full_path' => Storage::disk('local')->path($path),
                'file_exists' => file_exists(Storage::disk('local')->path($path))
            ]);
        }

        return response()->json(['error' => 'No file uploaded']);

    } catch (\Exception $e) {
        \Log::error('Test upload failed: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()]);
    }
});




// Add this temporary route to routes/web.php to test your specific CSV file:

Route::get('/test-csv/{communityId}', function($communityId) {
    try {
        // Test with a small sample CSV
        $testCsvContent = "apartment_number;building_number;area\n1;A;50.5\n2;A;45.2\n3;B;60.0";
        $testFilePath = storage_path('app/test_apartments.csv');
        file_put_contents($testFilePath, $testCsvContent);

        \Log::info('Created test CSV:', [
            'content' => $testCsvContent,
            'file_path' => $testFilePath
        ]);

        $importManager = app(\App\Services\Import\ImportManager::class);
        $stats = $importManager->import('apartments', $testFilePath, [
            'community_id' => (int)$communityId,
            'delimiter' => ';',
            'encoding' => 'UTF-8',
            'skip_header' => true,
        ]);

        // Clean up test file
        unlink($testFilePath);

        return response()->json([
            'status' => 'success',
            'test_csv_content' => $testCsvContent,
            'import_stats' => $stats
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

// Also add this route to test your actual CSV structure without importing:
Route::post('/analyze-csv', function(\Illuminate\Http\Request $request) {
    try {
        if (!$request->hasFile('csv_file')) {
            return response()->json(['error' => 'No file uploaded']);
        }

        $file = $request->file('csv_file');
        $content = file_get_contents($file->getPathname());

        // Test different delimiters
        $delimiters = [';' => 'semicolon', ',' => 'comma', "\t" => 'tab', '|' => 'pipe'];
        $analysis = [];

        foreach ($delimiters as $delimiter => $name) {
            $lines = explode("\n", $content);
            $firstLine = isset($lines[0]) ? str_getcsv($lines[0], $delimiter) : [];
            $secondLine = isset($lines[1]) ? str_getcsv($lines[1], $delimiter) : [];

            $analysis[$name] = [
                'delimiter' => $delimiter,
                'first_line_columns' => count($firstLine),
                'first_line' => $firstLine,
                'second_line_columns' => count($secondLine),
                'second_line' => $secondLine,
                'looks_good' => count($firstLine) > 1 && count($secondLine) === count($firstLine)
            ];
        }

        return response()->json([
            'file_size' => strlen($content),
            'total_lines' => count(explode("\n", $content)),
            'first_100_chars' => substr($content, 0, 100),
            'delimiter_analysis' => $analysis,
            'encoding_detected' => mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'ISO-8859-2', 'Windows-1250'], true)
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

// Create a simple HTML form for the CSV analyzer:
Route::get('/analyze-csv', function() {
    return '
    <!DOCTYPE html>
    <html>
    <head><title>CSV Analyzer</title></head>
    <body>
        <h2>CSV File Analyzer</h2>
        <form action="/analyze-csv" method="POST" enctype="multipart/form-data">
            ' . csrf_field() . '
            <input type="file" name="csv_file" accept=".csv" required>
            <button type="submit">Analyze CSV</button>
        </form>
    </body>
    </html>';
});
