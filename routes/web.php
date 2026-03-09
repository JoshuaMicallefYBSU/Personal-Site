<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VATPAC\VATPACController;
use App\Http\Controllers\CTP\CTPController;
use App\Http\Controllers\TestController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test/vatsim-api', [TestController::class, 'Job'])->name('vatsimapi'); // Local Running Only

// VATPAC Section
Route::prefix('vatpac')->group(function () {
    Route::prefix('events')->group(function () {
        Route::get('iron-mic-leaderboard', [VATPACController::class, 'ironMicView'])->name('vatpac.events.ironmic');
    });
});

Route::prefix('ctp')->group(function () {
    Route::prefix('oceanic')->group(function () {
        Route::get('dashboard', [CTPController::class, 'dashboardView'])->name('ctp.dashboard');
    });
});