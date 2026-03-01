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
            return response()->json($request->user());
        }
        );

        Route::post('/logout', [ApiAuthController::class , 'logout']);
    });
