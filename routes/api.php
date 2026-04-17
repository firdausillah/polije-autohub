<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Public\VehicleController;

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/vehicles/{kode}', [VehicleController::class, 'show']);
});