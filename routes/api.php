<?php

use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\SkpiCategoryApiController;
use App\Http\Controllers\PublicHubController;
use Illuminate\Support\Facades\Route;

// RESTful API v1 (Khusus Admin via Bearer Token Sanctum — untuk integrasi sistem & pengujian Postman)
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('login', [AuthApiController::class, 'login'])->middleware('throttle:login-attempt')->name('login');

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('me', [AuthApiController::class, 'me'])->name('me');
        Route::post('logout', [AuthApiController::class, 'logout'])->name('logout');

        Route::apiResource('skpi-categories', SkpiCategoryApiController::class);

        // Sinkronisasi SIAKAD khusus Admin via Bearer Token
        Route::get('siakad/students', [PublicHubController::class, 'siakadJsonApi'])
            ->name('siakad.students');
    });
});
