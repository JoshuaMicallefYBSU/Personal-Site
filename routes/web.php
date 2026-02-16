<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VATPACController;

Route::get('/', function () {
    return view('welcome');
});

// VATPAC Section
Route::prefix('vatpac')->group(function () {
    Route::prefix('events')->group(function () {
        Route::get('iron-mic-leaderboard', [VATPACController::class, 'ironMicView'])->name('vatpac.events.ironmic');
    });
});