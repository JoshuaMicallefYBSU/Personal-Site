<?php

use App\Http\Controllers\VATPAC\TafController;
use Illuminate\Support\Facades\Route;

Route::get('/vatpac/taf-raw', [TafController::class, 'show']);
Route::get('/', function () {
    return response(
        'TAF XML API is running. Use /api/taf?ids=YSSY',
        200,
        ['Content-Type' => 'text/plain; charset=utf-8']
    );
});