<?php

use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\SkpiCategoryApiController;
use App\Http\Controllers\PublicHubController;
use Illuminate\Support\Facades\Route;

// RESTful API v1 (Autentikasi Bearer Token Sanctum — khusus integrasi sistem & pengujian Postman)
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('login', [AuthApiController::class, 'login'])->middleware('throttle:login-attempt')->name('login');

    Route::apiResource('skpi-categories', SkpiCategoryApiController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthApiController::class, 'me'])->name('me');
        Route::post('logout', [AuthApiController::class, 'logout'])->name('logout');

        // Sinkronisasi SIAKAD hanya boleh diakses Admin via Bearer Token
        Route::get('siakad/students', [PublicHubController::class, 'siakadJsonApi'])
            ->middleware('role:admin')
            ->name('siakad.students');
    });
});
