<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GpsController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\NoCache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\NotificationController;
use App\Services\NotificationService;

Route::get('/tes-email', function () {
    // Ambil data user dari session
    $userData = session('user_data');
    
    // Ambil id_perangkat (atau gunakan ID default/dummy jika belum ada di session)
    $idPerangkat = is_array($userData) 
        ? ($userData['id_perangkat'] ?? 'perangkat_1') 
        : ($userData->id_perangkat ?? 'perangkat_1');

    $userEmail = is_array($userData) 
        ? ($userData['email'] ?? null) 
        : ($userData->email ?? null);

    // Jika di session belum ada email, masukkan email aktif kamu
    if (!$userEmail) {
        $userEmail = 'handynandaf@gmail.com';
    }

    // Koordinat contoh area Kuningan (-6.9772, 108.4831)
    $lat = -6.9772;
    $lng = 108.4831;

    // Kirim notifikasi sesuai signature method NotificationService terbaru
    NotificationService::send(
        idPerangkat: $idPerangkat,
        pesanNotifikasi: 'Pelanggaran Zona Geofencing! Kendaraan terdeteksi keluar dari radius lokasi aman.',
        tipeNotifikasi: 'danger',
        userEmail: $userEmail,
        lat: $lat,
        lng: $lng
    );

    return "Berhasil! Notifikasi dikirim untuk ID Perangkat: <b>{$idPerangkat}</b> dan email dikirim ke: <b>{$userEmail}</b>. Silakan cek Inbox Gmail dan node <code>/GPS_TRACKING/NOTIFIKASI_LOG/{$idPerangkat}</code> di Firebase!";
});

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
        // 1. Ambil ID perangkat (misal dari session atau default)
        $idPerangkat = session('id_perangkat', 'perangkat_1'); 

        // 2. Ambil data dari Firebase (sesuaikan dengan method/helper Firebase kamu)
        $notifications = []; // Misal: FirebaseService::getNotifications($idPerangkat);

        // 3. Kirim data ke View Notifikasi
        return view('notifikasi', [
            'notifications' => $notifications ?? [], // Kuncinya ada di '?? []' agar tidak error null
            'idPerangkat'   => $idPerangkat,
        ]);
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

    // Route untuk aksi notifikasi
    Route::get('/notifikasi/{idPerangkat?}', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/{idPerangkat}/read/{pushId}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifikasi/{idPerangkat}/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::delete('/notifikasi/{idPerangkat}/delete/{pushId}', [NotificationController::class, 'destroy'])->name('notifications.destroy');    

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