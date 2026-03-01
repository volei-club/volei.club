<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;

Route::post('/login', [ApiAuthController::class , 'login']);
Route::post('/2fa/verify', [ApiAuthController::class , 'verify2fa']);
Route::post('/2fa/resend', [ApiAuthController::class , 'resend2fa']);

Route::post('/recuperare', [ApiAuthController::class , 'sendRecovery']);
Route::post('/resetare-parola', [ApiAuthController::class , 'resetPassword']);

// Protected routes using Sanctum token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
            // Returnăm userul complet plus club-ul său pentru UI-ul de profil
            return response()->json($request->user()->load('club'));
        }
        );

        Route::post('/logout', [ApiAuthController::class , 'logout']);

        // Rute de Management (Scopingul se face nivel de controllere în funcție de rol)
        Route::apiResource('clubs', \App\Http\Controllers\Api\ClubController::class);
        Route::apiResource('users', \App\Http\Controllers\Api\UserController::class);
        Route::apiResource('teams', \App\Http\Controllers\Api\TeamController::class);
        Route::apiResource('squads', \App\Http\Controllers\Api\SquadController::class);

        Route::post('/impersonate/{user}', [\App\Http\Controllers\Api\ImpersonationController::class , 'impersonate']);
        Route::post('/impersonate-leave', [\App\Http\Controllers\Api\ImpersonationController::class , 'leave']);
    });
