<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\VATPAC\VATPACController;
use App\Http\Controllers\CTP\CTPController;
use App\Http\Controllers\Focr\FocrController;
use App\Http\Controllers\TestController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.send');

Route::get('/test/vatsim-api', [TestController::class, 'Job'])->name('vatsimapi'); // Local Running Only

// VATPAC Section (Redirects as they are old links)
Route::prefix('vatpac')->group(function () {
    Route::prefix('events')->group(function () {
        Route::get('iron-mic-leaderboard', function () {
            return redirect('/vatpac/iron-mic');
        });
    });
});

Route::prefix('ctp')->group(function () {
    Route::prefix('oceanic')->group(function () {
        Route::get('dashboard', [CTPController::class, 'dashboardView'])->name('ctp.dashboard');
    });
});

Route::prefix('focr')->name('focr.')->group(function () {
    Route::get('/', [FocrController::class, 'index'])->name('index');
    Route::post('/', [FocrController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('store');
    Route::get('/delete/{token}', [FocrController::class, 'destroy'])->name('destroy');

    // Requires ?key=<FOCR_REDIRECT_KEY> from .env — the path is visible here, but it's
    // useless without the secret key, which is never committed to the repo.
    Route::get('/redirect', [FocrController::class, 'redirect'])->name('redirect');
});