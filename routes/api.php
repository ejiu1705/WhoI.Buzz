<?php

use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\SkpiCategoryApiController;
use App\Http\Controllers\PublicHubController;
use Illuminate\Support\Facades\Route;

// RESTful API v1 (Modul 4: Sanctum Bearer Token + API Resource + ForceJsonResponse)
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public Auth & Read Endpoints
    Route::post('login', [AuthApiController::class, 'login'])->middleware('throttle:login-attempt')->name('login');
    Route::get('siakad/students', [PublicHubController::class, 'siakadJsonApi'])->name('siakad.students');

    // Resource API (index/show public; store/update/destroy protected by #[Middleware('auth:sanctum')] + Policy)
    Route::apiResource('skpi-categories', SkpiCategoryApiController::class);

    // Token-Protected Identity Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthApiController::class, 'me'])->name('me');
        Route::post('logout', [AuthApiController::class, 'logout'])->name('logout');
    });
});
