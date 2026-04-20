<?php

use App\Http\Controllers\Api\Public\ServiceHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Public\VehicleController;

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/vehicles/{kode}', [VehicleController::class, 'show']);
    Route::get('/service-history', [ServiceHistoryController::class, 'show']);
});