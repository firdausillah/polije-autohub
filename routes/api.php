<?php

use App\Http\Controllers\Api\Public\CustomerAuthController;
use App\Http\Controllers\Api\Public\ServiceBookingRequests;
use App\Http\Controllers\Api\Public\ServiceHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Public\VehicleController;


Route::middleware('throttle:10,1')->group(function () {
    Route::post('/service-booking-requests', [ServiceBookingRequests::class, 'store']);
    Route::post('/login', [CustomerAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {  
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
        Route::get('/service-history', [ServiceHistoryController::class, 'show']);
        Route::get('/vehicles/{kode}', [VehicleController::class, 'show']); //kode akan diambilkan dari data orng yang login
    });
});