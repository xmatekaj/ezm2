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
    Route::get('/owner/dashboard', [DashboardController::class, 'ownerDashboard'])->name('owner.dashboard');
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
