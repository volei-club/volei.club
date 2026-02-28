<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashAuthController;

Route::get('/', function () {
    return view('home');
});

Route::get('/login', function () {
    return redirect()->route('dash.login');
})->name('login');

// Dashboard Authentication Routes
Route::prefix('dash')->group(function () {
    // Guest roots
    Route::middleware('guest')->group(function () {
            Route::get('/login', [DashAuthController::class , 'showLogin'])->name('dash.login');
            Route::post('/login', [DashAuthController::class , 'login']);

            Route::get('/recuperare', [DashAuthController::class , 'showRecovery'])->name('dash.recovery');
            Route::post('/recuperare', [DashAuthController::class , 'sendRecovery'])->name('password.email');

            Route::get('/resetare-parola/{token}', [DashAuthController::class , 'showResetForm'])->name('password.reset');
            Route::post('/resetare-parola', [DashAuthController::class , 'resetPassword'])->name('password.update');

            Route::get('/google/redirect', [DashAuthController::class , 'redirectToGoogle'])->name('dash.google.redirect');
            Route::get('/google/callback', [DashAuthController::class , 'handleGoogleCallback'])->name('dash.google.callback');

            Route::get('/2fa', [DashAuthController::class , 'show2fa'])->name('dash.2fa.show');
            Route::post('/2fa', [DashAuthController::class , 'verify2fa'])->name('dash.2fa.verify');
            Route::post('/2fa/resend', [DashAuthController::class , 'resend2fa'])->name('dash.2fa.resend');
        }
        );

        // Protected roots
        Route::middleware('auth')->group(function () {
            Route::get('/', [DashAuthController::class , 'index'])->name('dash.index');
            Route::post('/logout', [DashAuthController::class , 'logout'])->name('dash.logout');
        }
        );
    });
