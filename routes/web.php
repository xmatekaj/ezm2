<?php
// routes/web.php

use App\Http\Controllers\Auth\OwnerRegistrationController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Api\TerritorialApiController;

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

// Two-Factor Authentication Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::post('/two-factor/resend', [TwoFactorController::class, 'resend'])->name('two-factor.resend');
});

// API Routes for Territorial Data
Route::prefix('api')->group(function () {
    Route::get('/voivodeships', [TerritorialApiController::class, 'getVoivodeships']);
    Route::get('/cities/{voivodeship}', [TerritorialApiController::class, 'getCities']);
    Route::get('/streets/{voivodeship}/{city}', [TerritorialApiController::class, 'getStreets']);
});

// Dashboard (after login)
Route::middleware(['auth', 'verified.2fa'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
