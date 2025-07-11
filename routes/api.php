<?php

// Add to routes/api.php
use App\Http\Controllers\Api\TerritorialApiController;

Route::get('/voivodeships', [TerritorialApiController::class, 'getVoivodeships']);
Route::get('/cities/{voivodeship}', [TerritorialApiController::class, 'getCities']);
Route::get('/streets/{voivodeship}/{city}', [TerritorialApiController::class, 'getStreets']);