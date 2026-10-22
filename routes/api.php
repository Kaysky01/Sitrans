<?php

use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.key', 'throttle:api'])->prefix('v1')->group(function (): void {
    Route::get('menu', MenuController::class)->name('api.v1.menu');
    Route::post('orders', [OrderController::class, 'store'])->name('api.v1.orders.store');
});
