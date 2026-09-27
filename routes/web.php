<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GpsController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\NoCache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;

Route::middleware([NoCache::class])->group(function () {
    
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/dashboard', function () {
        if (!session()->has('is_logged_in')) {
            return redirect()->route('login');
        }
        
        $user = session('user_data');
        return view('dashboard', compact('user'));
    })->name('dashboard');

    Route::get('/notifikasi', function () {
        if (!session()->has('is_logged_in')) {
            return redirect()->route('login');
        }
        return view('notifikasi');
    })->name('notifikasi.index');

    Route::get('/perangkat', function () {
        if (!session()->has('is_logged_in')) {
            return redirect()->route('login');
        }
        return view('perangkat');
    })->name('perangkat.index');

    Route::get('/history', function () {
        if (!session()->has('is_logged_in')) {
            return redirect()->route('login');
        }
        return response()
            ->view('history')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    })->name('history.index');

    Route::get('/lokasi-aman', function () {
        if (!session()->has('is_logged_in')) {
            return redirect()->route('login');
        }
        return view('lokasi-aman');
    })->name('lokasi.aman');

    Route::get('/forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

    Route::post('/forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

    // Rute untuk menampilkan halaman form pembuatan password baru (DARI LINK EMAIL)
    Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
        ->middleware('guest')
        ->name('password.reset');

    // Rute untuk memproses/menyimpan password baru ke database
    Route::post('/reset-password', [App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
        ->middleware('guest')
        ->name('password.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('gps', GpsController::class);
    Route::resource('users', UserController::class);

    Route::get('/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);

    // Route::get('/register', function () {

    //     if (!session()->has('is_logged_in')) {
    //         return redirect()->route('login');
    //     }

    //     return view('auth.register');

    // })->name('register');
});

require __DIR__.'/auth.php';