<?php

use App\Http\Controllers\Api\V1\FuelLogController;
use Illuminate\Support\Facades\Route;

Route::middleware('check.api.key')->prefix('v1')->group(function () {
    Route::get('/fuel-logs', [FuelLogController::class, 'index']);
    Route::get('/fuel-logs/{id}', [FuelLogController::class, 'show']);
    Route::post('/fuel-logs', [FuelLogController::class, 'store']);
});