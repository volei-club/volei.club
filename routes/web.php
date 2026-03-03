<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashAuthController;

Route::get('/', function () {
    return view('home');
});

Route::get('/login', function () {
    return redirect()->route('dash.login');
})->name('login');

// Dashboard routes (Views only, logic handled via API)
Route::prefix('dash')->group(function () {
    Route::get('/login', [DashAuthController::class , 'showLogin'])->name('dash.login');

    // Mentine recuperarea/resetarea standard in web deocamdata, sau o putem porta imediat
    Route::get('/recuperare', [DashAuthController::class , 'showRecovery'])->name('dash.recovery');

    Route::get('/resetare-parola/{token}', [DashAuthController::class , 'showResetForm'])->name('password.reset');

    Route::get('/google/redirect', [DashAuthController::class , 'redirectToGoogle'])->name('dash.google.redirect');
    Route::get('/google/callback', [DashAuthController::class , 'handleGoogleCallback'])->name('dash.google.callback');

    Route::get('/2fa', [DashAuthController::class , 'show2fa'])->name('dash.2fa.show');

    // Dashboard root view. Protection will be done via frontend JS checking the API token.
    Route::get('/', [DashAuthController::class , 'index'])->name('dash.index');
    Route::get('/cluburi', [DashAuthController::class , 'index']);
    Route::get('/membri', [DashAuthController::class , 'index']);
    Route::get('/grupe', [DashAuthController::class , 'index']);
    Route::get('/echipe', [DashAuthController::class , 'index']);
    Route::get('/abonamente', [DashAuthController::class , 'index']);
    Route::get('/audit', [DashAuthController::class , 'index']);
    Route::get('/locatii', [DashAuthController::class , 'index']);
    Route::get('/antrenamente', [DashAuthController::class , 'index']);
    Route::get('/calendar', [DashAuthController::class , 'index']);
    Route::get('/sistem', [DashAuthController::class , 'index']);

    // Catch-all for any other dash sub-paths (SPA fallback)
    Route::get('/{any}', [DashAuthController::class , 'index'])->where('any', '.*');
});
