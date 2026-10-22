<?php

use App\Http\Controllers\ImpersonationController;
use App\Livewire\Auth\Register;
use App\Livewire\Pos\Cashier;
use App\Livewire\Pos\Receipt;
use App\Livewire\Public\Storefront;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('landing');

Route::get('/m/{tenant:slug}', Storefront::class)->name('storefront');

Route::middleware('auth')->group(function () {
    Route::get('/impersonate/{tenant}', [ImpersonationController::class, 'start'])->name('impersonate.start');
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');
});

Route::middleware(['guest', 'throttle:register'])->group(function () {
    Route::get('/register', Register::class)->name('register');
});

Route::middleware(['auth', 'tenant', 'tenant.active'])->group(function () {
    Route::get('/pos', Cashier::class)->middleware('can:pos.use')->name('pos');
    Route::get('/orders/{order}/receipt', Receipt::class)->middleware('can:orders.view')->name('receipt');
});
