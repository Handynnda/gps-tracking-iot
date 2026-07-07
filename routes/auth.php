<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Middleware\NoCache;
use Illuminate\Support\Facades\Route;

Route::middleware([NoCache::class])->group(function () {
    
    // Route Register
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Route Login
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Route Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

});